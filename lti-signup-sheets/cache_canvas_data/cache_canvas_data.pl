#!/usr/bin/perl -w

# Chris Warren @ Williams College - 2015/05

# This script fetches term, user, course, and enrollment data from the
# canvas course management system and puts it in a local database. It
# is designed to run on a repeated basis (e.g. a few times a day via
# cron) to maintain a local cache of this info.

#######################################################################
# NOTE: this process has little to no security! Values from curl calls
# are inserted directly into the DB inside SQL statement with minimal
# validation - this script considers everything it touches to be part
# of a trusted system
#######################################################################

use strict;
use warnings;

use Data::Dumper;

use DBI;
use DBD::mysql;

use FindBin qw($Bin);
use lib "$Bin/JSON-2.53/lib";

use JSON;

my $LOG_LEVEL = 3; # 0=run silently; 1=typical logging for cron-based runs; 2-3=richer logging level; 4-10=generally debugging/dev logging levels

our %CFG = ();
require "$Bin/CONFIG_cache_canvas_data.pl";

my $DBH = getDatabaseHandle();

my($day, $month, $year)=(localtime)[3,4,5];
$day = ($day<10?'0':'').$day;
$month++;
$month = ($month<10?'0':'').$month;
$year += 1900;
my $CURRENT_DATE = "$year-$month-$day".' 00:00:00-05:00';
my $CURRENT_DATE_TZ = "$year-$month-$day".'T00:00:00-05:00Z';

my $GLOBAL_curl_calls = 0;

myLog(1,"\n========================================================================\n========================================================================\n$CURRENT_DATE collect_canvas_data.pl START AT ".localtime());

#-----------------------------------------------------------------------
#-----------------------------------------------------------------------

# the actual update process looks something like this:

# handle the terms
# 1. get all the terms from canvas using a curl call
# 2. parse the json into a useable data structure
# 3. insert or update the DB records
# 4. compile a list/set of current terms

# handle the users
# 1. get all the users from canvas using a curl call
# 2. parse the json into a useable data structure
# 3. insert or update the DB records

# handle the courses
# 1. get all the courses from canvas using a curl call
# 2. parse the json into a useable data structure
# 3. foreach course
# 4.   a) update or insert it into the database
# 5.   b) compile the list of live courses (current date is before the end of the term of that course)
# ??? 6. query the DB to get the list of dead courses (courses for the current or terms that are not live)
# ??? 7. delete the dead courses

# handle the enrollments
# ??? 1. delete all enrollments for dead courses
# 2. for each live course
#    a) get the enrollments from canvas
#    b) parse the json into a useable data structure
#    c) get the enrollments from the DB
#    d) compare canvas data (canonical) and db data (mutable) to split the enrollments into insert, update, and delete sets
#    e) run the deletes
#    ???? f) run the updates - NOTE: I don't think there are actually any updates to run - just adds and deletes (update --> ignore)
#    g) run the inserts

my %live_terms = ();
my %terms_map_canvas_to_idstr = ();
my %live_courses = ();
my %live_users = ();
my %live_enrollments = ();

#-----------------------------------------------------------------------
#-----------------------------------------------------------------------

# handle the terms

# 0. get current term info from DB
my $terms_result = runSQL("SELECT * FROM terms WHERE flag_delete=0",$DBH);
my %db_terms = ();
while (my @db_term_data = $terms_result->fetchrow_array) {
    $db_terms{$db_term_data[1]} = {};
    $db_terms{$db_term_data[1]}->{'term_idstr'} = $db_term_data[1];
    $db_terms{$db_term_data[1]}->{'name'}       = $db_term_data[2];
    $db_terms{$db_term_data[1]}->{'start_date'} = $db_term_data[3];
    $db_terms{$db_term_data[1]}->{'end_date'}   = $db_term_data[4];
}
#print Dumper(\%db_terms);

# 1. get all the terms from canvas using a curl call
my $canvas_terms_json = curlCanvas("/accounts/$CFG{'CANVAS_ACCOUNT'}/terms");

# 2. parse the json into a useable data structure
my @canvas_terms = @{(decode_json($canvas_terms_json))->{'enrollment_terms'}};
#print Dumper(\@canvas_terms);
#exit;

# 3. insert or update the DB records
# 4. and compile a list/set of current terms
my $num_terms_canvas = 0;
my $num_terms_inserted = 0;
my $num_terms_updated = 0;
my $num_terms_live = 0;
foreach my $canvas_term_data (@canvas_terms) {
    $num_terms_canvas++;
    my $idstr = $canvas_term_data->{'sis_term_id'};
    my $name = $canvas_term_data->{'name'};
    my $start = $canvas_term_data->{'start_at'};
    my $end = $canvas_term_data->{'end_at'};
    
    $start =~ s/T/ /;
    $end =~ s/T/ /;
    $start =~ s/Z//;
    $end =~ s/Z//;

    my $canvas_term_id = $canvas_term_data->{'id'};

    if (! exists($db_terms{$canvas_term_id})) {
	$num_terms_inserted++;
	runSQL("INSERT INTO terms (term_idstr,name,start_date,end_date,canvas_term_id) VALUES ('$idstr','$name','$start','$end',$canvas_term_id)",$DBH);
    } else {
	$num_terms_updated++;
	runSQL("UPDATE terms SET name='$name',start_date='$start',end_date='$end',canvas_term_id=$canvas_term_id WHERE term_idstr='$idstr'",$DBH);
    }

    $terms_map_canvas_to_idstr{$canvas_term_id} = $idstr;

    if (1==0 || $end ge $CURRENT_DATE) {
	$num_terms_live++;
	$live_terms{$canvas_term_data->{'sis_term_id'}} = $canvas_term_id;
    }
}

#print Dumper(\%live_terms);
#print Dumper(\%terms_map_canvas_to_idstr);
#exit;
myLog(1,"*  terms from canvas: $num_terms_canvas; terms inserted: $num_terms_inserted; terms updated: $num_terms_updated; live terms: $num_terms_live (".join(",",keys(%live_terms)).")");
#exit;

#-----------------------------------------------------------------------
#-----------------------------------------------------------------------

# handle the users
# 0. get current users info from DB
my $users_result = runSQL("SELECT * FROM users WHERE flag_delete=0",$DBH);
my %db_users = ();
while (my @db_user_data = $users_result->fetchrow_array) {
    #print Dumper(\@db_user_data);
    #exit;
    my $username = $db_user_data[3];
    $db_users{$username} = {};
    $db_users{$username}->{'user_id'} = $db_user_data[0];

    $db_users{$username}->{'canvas_user_id'} = $db_user_data[1];
    $db_users{$username}->{'sis_user_id'} = $db_user_data[2];

    $db_users{$username}->{'username'} = $db_user_data[3];
    $db_users{$username}->{'email'} = $db_user_data[4];
    $db_users{$username}->{'first_name'} = $db_user_data[5];
    $db_users{$username}->{'last_name'} = $db_user_data[6];
    $db_users{$username}->{'created_at'} = $db_user_data[7];
    $db_users{$username}->{'updated_at'} = $db_user_data[8];
    $db_users{$username}->{'flag_is_system_admin'} = $db_user_data[9];
    $db_users{$username}->{'flag_is_banned'} = $db_user_data[10];
    $db_users{$username}->{'flag_delete'} = $db_user_data[11];
}
#print Dumper(\%db_users);
#exit;

# 1. get all the users from canvas using a curl call
# 2. parse the json into a useable data structure
my @canvas_users = ();
getCanvasUsersArray(\@canvas_users);
#exit;
#print Dumper(\@canvas_users);
myLog(3,"num canvas users is ".(scalar @canvas_users));
#exit;

# 3. insert or update the DB records
my $num_users_canvas = 0;
my $num_users_inserted = 0;
my $num_users_updated = 0;
my $num_users_live = 0;
my $num_bad_sis_ids = 0;
foreach my $canvas_user_data (@canvas_users) {
    $num_users_canvas++;
    my $username = $canvas_user_data->{'login_id'};
    my $canvas_id = $canvas_user_data->{'id'};
    my $sis_id = $canvas_user_data->{'sis_user_id'};
    my $sort_name = $canvas_user_data->{'sortable_name'};

    my @name_parts = split(',',$sort_name);
    my $first_name = trim($name_parts[1]);
    my $last_name = trim($name_parts[0]);
    my $email = $username.'@williams.edu';

    $first_name =~ s/\'/\'\'/g;
    $last_name =~ s/\'/\'\'/g;

    if ((! $sis_id) || ($sis_id =~ /\D/)) {
	$sis_id = '0';
	$num_bad_sis_ids++;
    }
    
    if (! exists($db_users{$username})) {
	runSQL("INSERT INTO users (canvas_user_id,sis_user_id,username,email,first_name,last_name,created_at,updated_at) VALUES ($canvas_id,$sis_id,'$username','$email','$first_name','$last_name',NOW(),NOW())",$DBH);
	$num_users_inserted++;
    } else {
	if (($db_users{$username}{'canvas_user_id'} ne $canvas_id) || 
	    ($db_users{$username}{'sis_user_id'} ne $sis_id) || 
	    ($db_users{$username}{'first_name'} ne $first_name) || 
	    ($db_users{$username}{'last_name'} ne $last_name)
	    )
	{
	    runSQL("UPDATE users SET canvas_user_id=$canvas_id,sis_user_id=$sis_id,username='$username',email='$email',first_name='$first_name',last_name='$last_name',updated_at=NOW() WHERE username='$username'",$DBH);
	    $num_users_updated++;
	}
    }

    my $users_result = runSQL("SELECT * FROM users WHERE flag_delete=0",$DBH);
    if (exists($db_users{$username})) {
	$live_users{$canvas_id} = $db_users{$username}{'user_id'};
	$num_users_live++;
    } else {
	my $user_result = runSQL("SELECT * FROM users WHERE username='$username' AND flag_delete=0",$DBH);
	my @db_user_data = $user_result->fetchrow_array;
	if ($db_user_data[0]) {
	    $live_users{$canvas_id} = $db_user_data[0];
	    $num_users_live++;
	}
    }
}
#print Dumper(\%live_users);
#exit;
myLog(1,"*  users from canvas: $num_users_canvas; users inserted: $num_users_inserted; users updated: $num_users_updated; live users: $num_users_live; bad user sis ids: $num_bad_sis_ids");

#-----------------------------------------------------------------------
#-----------------------------------------------------------------------

# handle the courses

# 0. get current courses info from DB
my $courses_result = runSQL("SELECT * FROM courses WHERE flag_delete=0",$DBH);
my %db_courses = ();
while (my @db_course_data = $courses_result->fetchrow_array) {
    #print Dumper(\@db_course_data);
    #exit;
    my $canvas_course_id = $db_course_data[6];
    $db_courses{$canvas_course_id} = {};
    $db_courses{$canvas_course_id}->{'course_idstr'} = $db_course_data[1];
    $db_courses{$canvas_course_id}->{'short_name'} = $db_course_data[2];
    $db_courses{$canvas_course_id}->{'long_name'} = $db_course_data[3];
    $db_courses{$canvas_course_id}->{'account_idstr'} = $db_course_data[4];
    $db_courses{$canvas_course_id}->{'term_idstr'} = $db_course_data[5];
    $db_courses{$canvas_course_id}->{'canvas_course_id'} = $db_course_data[6];
    $db_courses{$canvas_course_id}->{'begins_at_str'} = $db_course_data[7];
    $db_courses{$canvas_course_id}->{'ends_at_str'} = $db_course_data[8];
}
#print Dumper(\%db_courses);
#exit;

# 1 fetch all the organizations (regardless of term stuff)
# 1.5 get all the courses from canvas using a curl call
# 2. parse the json into a useable data structure
my @canvas_courses = ();

getCanvasCoursesForSubAccounts(\@canvas_courses);

# print Dumper(\@canvas_courses);
# exit;

#foreach my $term_canvas_id (values(%live_terms)) {
foreach my $live_term (keys(%live_terms)) {
    myLog(3,"getting courses for live term $live_term, canvas term id: $live_terms{$live_term}");
#    getCanvasCoursesArray($term_canvas_id,\@canvas_courses);
    getCanvasCoursesArray($live_terms{$live_term},\@canvas_courses);
}


#curl 'https://williams.instructure.com/api/v1/accounts/98616/courses' -X GET -F 'per_page=100' -F 'page=1' -F 'by_subaccounts[]=131002' -F 'by_subaccounts[]=137352' -H 'Authorization: Bearer <TOKEN_HERE>'


#print Dumper(\@canvas_courses);
myLog(3,"canvas course count is ".(scalar @canvas_courses));
#exit;


# 3. foreach course from canvas
my $num_courses_canvas = 0;
my $num_courses_inserted = 0;
my $num_courses_updated = 0;
my $num_courses_live = 0;
foreach my $canvas_course_data (@canvas_courses) {
    $num_courses_canvas++;
    #print Dumper($canvas_course_data);
    #exit;
#    my $course_idstr = $canvas_course_data->{'course_code'};
    my $course_idstr = $canvas_course_data->{'sis_course_id'};
    if (! $course_idstr) {
	$course_idstr = 'UNKNOWN';
    }
    $course_idstr =~ s/\'/\'\'/g;

    my $short_name = $canvas_course_data->{'name'};
    $short_name =~ s/\'/\'\'/g;
    my $long_name = $short_name;

    my $term_idstr = $terms_map_canvas_to_idstr{$canvas_course_data->{'enrollment_term_id'}};

    my $canvas_course_id = $canvas_course_data->{'id'};
    my $begins_at_str = $canvas_course_data->{'start_at'};
    my $ends_at_str = $canvas_course_data->{'end_at'};

    if (! $term_idstr) {
	$term_idstr = '';
    }
    if (! $begins_at_str) {
	$begins_at_str = '';
    }
    if (! $ends_at_str) {
	$ends_at_str = '';
    }
    
# 4.   a) update or insert it into the database
    if (! exists($db_courses{$canvas_course_id})) {
	runSQL("INSERT INTO courses (course_idstr,short_name,long_name,account_idstr,term_idstr,canvas_course_id,begins_at_str,ends_at_str) VALUES ('$course_idstr','$short_name','$long_name','courses','$term_idstr',$canvas_course_id,'$begins_at_str','$ends_at_str')",$DBH);
	$num_courses_inserted++;
    } else {
	if (($db_courses{$canvas_course_id}{'course_idstr'} ne $course_idstr) || 
	    ($db_courses{$canvas_course_id}{'short_name'} ne $short_name) || 
	    ($db_courses{$canvas_course_id}{'term_idstr'} ne $term_idstr) || 
	    ($db_courses{$canvas_course_id}{'begins_at_str'} ne $begins_at_str) || 
	    ($db_courses{$canvas_course_id}{'ends_at_str'} ne $ends_at_str)
	    )
	{
	    runSQL("UPDATE courses SET course_idstr='$course_idstr',short_name='$short_name',long_name='$long_name',term_idstr='$term_idstr',begins_at_str='$begins_at_str',ends_at_str='$ends_at_str' WHERE canvas_course_id='$canvas_course_id'",$DBH);
	    $num_courses_updated++;
	}
    }

# 5.   b) compile the list of live courses (current date is before the end of the term of that course)
    # if the term is present, use that; otherwise, use the end date if that's present, otherwise consider it live (i.e. be inclusive)
    # NOTE: "live-ness" of a course indicates that the course
    # enrollment will be fetched from canvas and updated in the
    # database; i.e. non-live courses enrollments are skipped, not
    # deleted or otherwise altered
    if ($term_idstr) {
	if (exists($live_terms{$term_idstr})) {
	    $live_courses{$canvas_course_id} = $course_idstr;
	    $num_courses_live++;
	}
    } else {
	if ($ends_at_str) {
	    if ($ends_at_str gt $CURRENT_DATE_TZ) {
		$live_courses{$canvas_course_id} = $course_idstr;
		$num_courses_live++;
	    }
	} else {
	    $live_courses{$canvas_course_id} = $course_idstr;
	    $num_courses_live++;
	}
    }

} # end foreach my $canvas_course_data (@canvas_courses) 

#print Dumper(\%live_courses);
#exit;


# ??? 6. query the DB to get the list of dead courses (courses for the current or terms that are not live)
# ??? 7. delete the dead courses

myLog(1,"*  courses from canvas: $num_courses_canvas; courses inserted: $num_courses_inserted; courses updated: $num_courses_updated; live courses: $num_courses_live");

#-----------------------------------------------------------------------
#-----------------------------------------------------------------------

# handle the enrollments
# ??? 1. delete all enrollments for dead courses

# 2. for each live course
my $enrollments_canvas = 0;
my $enrollments_inserts = 0;
my $enrollments_deletes = 0;
foreach my $live_course_canvas_id (keys(%live_courses)) {
    myLog(4,"live course canvas id: $live_course_canvas_id");

#    a) get the enrollments from the DB
    my $enrollments_result = runSQL("SELECT * FROM enrollments WHERE canvas_course_id=$live_course_canvas_id AND flag_delete=0",$DBH);
    my %db_enrollments = ();
    while (my @db_enrollment_data = $enrollments_result->fetchrow_array) {
	#print Dumper(\@db_enrollment_data);
	#exit;
	my $combo_key = $live_course_canvas_id.'-'.$db_enrollment_data[1].'-'.$db_enrollment_data[3];
	$db_enrollments{$combo_key} = {};
	$db_enrollments{$combo_key}->{'enrollment_id'} = $db_enrollment_data[0];
	$db_enrollments{$combo_key}->{'canvas_user_id'} = $db_enrollment_data[1];
	$db_enrollments{$combo_key}->{'canvas_course_id'} = $db_enrollment_data[2];
	$db_enrollments{$combo_key}->{'canvas_role_name'} = $db_enrollment_data[3];
	$db_enrollments{$combo_key}->{'course_idstr'} = $db_enrollment_data[4];
#	$db_enrollments{$combo_key}->{'user_id'} = $db_enrollment_data[5];
	$db_enrollments{$combo_key}->{'course_role_name'} = $db_enrollment_data[6];
	$db_enrollments{$combo_key}->{'section_idstr'} = $db_enrollment_data[7];
    }
    #print Dumper(\%db_enrollment);
    #exit;


#    b) get the enrollments from canvas
#    c) parse the json into a useable data structure
    my @canvas_enrollments = ();
    getCanvasEnrollmentsForCoursesArray($live_course_canvas_id,\@canvas_enrollments);
#    print Dumper(\@canvas_enrollments);
    myLog(4,"for course $live_course_canvas_id the enrollment count is ".(scalar @canvas_enrollments));
    #exit;


#    d) compare canvas data (canonical) and db data (mutable) to split the enrollments into insert, and delete sets
    my %inserts = ();
    foreach my $canvas_enrollment (@canvas_enrollments) {
	$enrollments_canvas++;
	my $combo_key = $canvas_enrollment->{'course_id'}.'-'.$canvas_enrollment->{'user_id'}.'-'.$canvas_enrollment->{'role'};
	myLog(6,"enrollment combo key: $combo_key");
	if (exists($db_enrollments{$combo_key})) {
	    delete($db_enrollments{$combo_key}); #remove existing ones from the hash - the ones left in db_enrollments will be deleted
	} else { # combo key not currently in the db, so insert it
	    $inserts{$combo_key} = {};
	    $inserts{$combo_key}->{'canvas_user_id'} = $canvas_enrollment->{'user_id'};
	    $inserts{$combo_key}->{'canvas_course_id'} = $canvas_enrollment->{'course_id'};
	    $inserts{$combo_key}->{'canvas_role_name'} = $canvas_enrollment->{'role'};
	    $inserts{$combo_key}->{'course_idstr'} = $live_courses{$canvas_enrollment->{'course_id'}};
#	    $inserts{$combo_key}->{'user_id'} = $live_users{$canvas_enrollment->{'user_id'}};

	    if ($inserts{$combo_key}->{'canvas_role_name'} eq 'TeacherEnrollment') {
		$inserts{$combo_key}->{'course_role_name'} = 'teacher';
	    } else {
		$inserts{$combo_key}->{'course_role_name'} = 'student';
	    }
	    $inserts{$combo_key}->{'section_idstr'} = $inserts{$combo_key}->{'course_idstr'};
	}
    }

    #print Dumper(\%db_enrollments);
    #print Dumper(\%inserts);
    #exit;

#    e) run the deletes
    my @delete_enrollment_ids = map { $db_enrollments{$_}->{'enrollment_id'} } keys(%db_enrollments);
    if (@delete_enrollment_ids) {
	my $delete_enrollments_result = runSQL("UPDATE enrollments SET flag_delete=1 WHERE enrollment_id IN (".join(',',@delete_enrollment_ids).")",$DBH);
	$enrollments_deletes += $#delete_enrollment_ids+1;
    }

#    ???? f) run the updates - NOTE: I don't think there are actually any updates to run - just adds and deletes (update --> ignore)

#    g) run the inserts
#    my $insert_enrollments_sql = "INSERT INTO enrollments (canvas_user_id,canvas_course_id,canvas_role_name,course_idstr,user_id,course_role_name,section_idstr)";
    my $insert_enrollments_sql = "INSERT INTO enrollments (canvas_user_id,canvas_course_id,canvas_role_name,course_idstr,course_role_name,section_idstr)";
    my $values_prefix = ' VALUES ';
    my $num_inserted = 0;
    foreach my $ins_key (keys(%inserts)) {
	my $e = $inserts{$ins_key};
#	if (! $e->{'user_id'}) {
#	    $e->{'user_id'} = -1;
#	}
	$insert_enrollments_sql .= "$values_prefix($e->{'canvas_user_id'},$e->{'canvas_course_id'},'$e->{'canvas_role_name'}','$e->{'course_idstr'}','$e->{'course_role_name'}','$e->{'section_idstr'}')";
	$values_prefix = ' ,';
	$num_inserted++;
    }
    if ($num_inserted > 0) {
	my $insert_enrollments_result = runSQL($insert_enrollments_sql,$DBH);
	$enrollments_inserts += $num_inserted;
    }
}
myLog(1,"*  enrollments from canvas: $enrollments_canvas; enrollments inserted: $enrollments_inserts; enrollments deleted: $enrollments_deletes");

myLog(1,"*  total curl calls: $GLOBAL_curl_calls");

myLog(1,"\ncollect_canvas_data.pl STOP AT ".localtime()."\n========================================================================\n========================================================================");

exit;

#-----------------------------------------------------------------------
#-----------------------------------------------------------------------
#-----------------------------------------------------------------------
#-----------------------------------------------------------------------

sub myLog {
    my ($level,$msg) = @_;
    if ($level <= $LOG_LEVEL) {
	print "$msg\n";
    }
}

sub getDatabaseHandle {
    my $dbh = DBI->connect("dbi:mysql:$CFG{'DB_NAME'};$CFG{'DB_HOST'}",$CFG{'DB_USER'},$CFG{'DB_PASS'})
	or die "Connection Error: $DBI::errstr when connecting to dbi:mysql:$CFG{'DB_NAME'};$CFG{'DB_HOST'} as $CFG{'DB_USER'}\n";

    return $dbh;
}

#-----------------------------------------------------------------------

sub runSQL {
    my ($sql,$dbh) = @_;

    myLog(9,"\n-----------\n$sql\n-----------");

    my $sth = $dbh->prepare($sql);
    $sth->execute or die "SQL Error: $DBI::errstr on statement $sql\n";
    
    return $sth;
}

#-----------------------------------------------------------------------

sub curlCanvas {
    my ($rest_call) = @_;

    my $uri = "https://$CFG{'CURL_TARGET_HOST'}/api/v1".$rest_call;

    my $curl_call = "-s -H '$CFG{'CURL_AUTH_TOKEN'}' $uri";

    myLog(8,"\n-----------\ncurl $curl_call\n-----------");
#    if ($rest_call =~ /user/) {
#	exit;
#    }

    my $res = `curl $curl_call`;

    $GLOBAL_curl_calls++;

    return $res;
}

#-----------------------------------------------------------------------

sub trim {
    my ($str) = @_;
    if (! $str) {
	$str = '';
    }

    $str =~ s/^\s+|\s+$//g;

    return $str;
}

#-----------------------------------------------------------------------

sub getCanvasUsersArray {
    my ($users_array_ref) = @_;

    # loop to fetch 100 at a time (canvas API limit), accumulating until none returned
    my @user_set = ();
    my $users_page = 1;
    while ($users_page < 100) {
	# 1. get the users from canvas using a curl call
	my $canvas_users_json = curlCanvas("/accounts/$CFG{'CANVAS_ACCOUNT'}/users?page=$users_page\\&per_page=100");

	myLog(10,"canvas users JSON:\n$canvas_users_json");
	#exit;
    
        # 2. parse the json into a useable data structure
	# done if it's empty, otherwise append it to our accumulator
#	@user_set = ();
#	if (! (0 === strpos($canvas_users_json,'(end of string)'))) {
	    @user_set = @{(decode_json($canvas_users_json))};
#	}
	myLog(10,"page # $users_page; user set has ".(scalar @user_set));
	if (! @user_set) {
	    last;
	} else {
	    push(@$users_array_ref,@user_set);
	}
	#print Dumper(\@canvas_users);
	#exit;

	sleep(2); # no DOS attack simulator...

	$users_page++;
    }
}


#-----------------------------------------------------------------------

sub getCanvasCoursesArray {
    my ($term_canvas_id,$courses_array_ref) = @_;

    # loop to fetch 100 at a time, accumulating until none returned
    my @course_set = ();
    my $courses_page = 1;
    while ($courses_page < 100) {

	# 1. get the courses from canvas using a curl call
	my $canvas_courses_json = curlCanvas("/accounts/$CFG{'CANVAS_ACCOUNT'}/courses?page=$courses_page\\&per_page=100\\&published=true\\&enrollment_term_id=$term_canvas_id");
	myLog(10,"canvas courses JSON:\n$canvas_courses_json");
	#exit;
    
        # 2. parse the json into a useable data structure
	# done if it's empty, otherwise append it to our accumulator
	@course_set = @{(decode_json($canvas_courses_json))};
	myLog(10,"page # $courses_page; course set has ".(scalar @course_set));
	if (! @course_set) {
	    last;
	} else {
	    push(@$courses_array_ref,@course_set);
	}
	#print Dumper(\@canvas_courses);
	#exit;

	sleep(2); # no DOS attack simulator...

	$courses_page++;
    }
}

sub getCanvasCoursesForSubAccounts {
    my ($courses_array_ref) = @_;    

    # loop to fetch 100 at a time, accumulating until none returned
    my @course_set = ();
    my $courses_page = 1;
    while ($courses_page < 100) {

	# 1. get the courses from canvas using a curl call

	my $curl_call = "curl -s -H '$CFG{'CURL_AUTH_TOKEN'}' -X GET -F 'per_page=100' -F 'page=$courses_page'";
	foreach my $sub_acct_name (keys(%{$CFG{'CANVAS_SUBACCOUNTS'}})) {
	    $curl_call .= " -F 'by_subaccounts[]=".$CFG{'CANVAS_SUBACCOUNTS'}->{$sub_acct_name}."'";
	}
	$curl_call .= " 'https://$CFG{'CURL_TARGET_HOST'}/api/v1/accounts/$CFG{'CANVAS_ACCOUNT'}/courses'";

	myLog(8,"\n-----------\ncurl $curl_call\n-----------");

	my $canvas_courses_json = `curl $curl_call`;
	$GLOBAL_curl_calls++;
	
	myLog(10,"canvas courses JSON:\n$canvas_courses_json");
	#exit;
    
        # 2. parse the json into a useable data structure
	# done if it's empty, otherwise append it to our accumulator
	@course_set = @{(decode_json($canvas_courses_json))};
	myLog(10,"page # $courses_page; course set has ".(scalar @course_set));
	if (! @course_set) {
	    last;
	} else {
	    push(@$courses_array_ref,@course_set);
	}
	#print Dumper(\@canvas_courses);
	#exit;

	sleep(2); # no DOS attack simulator...

	@course_set = ();
	$courses_page++;
    }
}

#-----------------------------------------------------------------------

sub getCanvasEnrollmentsForCoursesArray {
    my ($canvas_course_id,$enrollments_array_ref) = @_;

    # loop to fetch 100 at a time, accumulating until none returned
    my @enrollment_set = ();
    my $enrollments_page = 1;
    while ($enrollments_page < 100) {

	# 1. get the enrollments from canvas using a curl call
	my $canvas_enrollments_json = curlCanvas("/courses/$canvas_course_id/enrollments?page=$enrollments_page\\&per_page=100");
	myLog(10,"cavnas enrollment JSON:\n$canvas_enrollments_json");
	#exit;
    
        # 2. parse the json into a useable data structure
	# done if it's empty, otherwise append it to our accumulator
	@enrollment_set = @{(decode_json($canvas_enrollments_json))};
	myLog(10,"page # $enrollments_page; enrollment set has ".(scalar @enrollment_set));
	if (! @enrollment_set) {
	    last;
	} else {
	    push(@$enrollments_array_ref,@enrollment_set);
	}
	#print Dumper(\@canvas_enrollments);
	#exit;

	sleep(2); # no DOS attack simulator...

	$enrollments_page++;
    }
}

