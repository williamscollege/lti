<?php
	require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('help'));
	require_once('../app_head.php');


	if ($IS_AUTHENTICATED) {
		echo "<div>";
		echo "<h3>" . $pageTitle . "</h3>";
		echo "<p>NOTE: Below is temporary text from Moodle help file</p>";

		// NOTE: START placeholder text from old Moodle Signup Sheets help file
		?>
		<ul>
			<li><a href="#SignupSheets-WhatareSignupSheets">What are Sign-up Sheets?</a></li>
			<li><a href="#SignupSheets-WhocanuseSignupSheets">Who can use Sign-up Sheets?</a></li>
			<li><a href="#SignupSheets-Howdotheywork">How do they work?</a>
				<ul>
					<li><a href="#SignupSheets-ConfiguringtheBlockonCoursePages">Configuring the Block on Course Pages</a></li>
					<li><a href="#SignupSheets-AvailableOpenings">Available Openings</a></li>
					<li><a href="#SignupSheets-MySignups">My Signups</a>
						<ul>
							<li><a href="#SignupSheets-IveSignedUpFor">I've Signed Up For</a></li>
							<li><a href="#SignupSheets-SignupsForMe">Sign-ups For Me</a></li>
						</ul>
					</li>
					<li><a href="#SignupSheets-SheetAdmin">Sheet Admin</a>
						<ul>
							<li><a href="#SignupSheets-SheetGroups">Sheet Groups</a></li>
							<li><a href="#SignupSheets-Sheets">Sheets</a>
								<ul>
									<li><a href="#SignupSheets-CreatinganewSignupSheet">Creating a new Sign-up Sheet</a></li>
									<li><a href="#SignupSheets-EditingaSignupSheet">Editing a Sign-up Sheet</a>
										<ul>
											<li><a href="#SignupSheets-SheetAccess">Sheet Access</a></li>
											<li><a href="#SignupSheets-CreatingOpenings">Creating Openings</a></li>
											<li><a href="#SignupSheets-DeletingOpenings">Deleting Openings</a></li>
											<li><a href="#SignupSheets-EditingOpeningsSigningOthersUpandEmailingSignedUpPeople">Editing Openings, Signing Others
													Up, and
													Emailing Signed Up People</a></li>
										</ul>
									</li>
								</ul>
							</li>
						</ul>
					</li>
				</ul>
			</li>
		</ul>

		<h1><a name="SignupSheets-WhatareSignupSheets"></a>What are Sign-up Sheets?</h1>

		<p><?php echo LMS_DOMAIN; ?> has a tool called Sign-up Sheets. This tool lets any user create a sheet with openings at specific times, and then allows
			other
			users to sign up for those openings. This is analogous to a list of times and dates on a piece of paper that is passed around or posted on a door
			and on
			which people would put their name - e.g. signing up for a particular lab slot, scheduling office hours, picking a study group time, or more general
			things
			like planning a party.
			<br clear="all" /></p>

		<h1><a name="SignupSheets-WhocanuseSignupSheets"></a>Who can use Sign-up Sheets?</h1>

		<p>Anyone with a <?php echo LMS_DOMAIN; ?> account can create signup sheets and can sign up for sheets others have created (if they've been granted
			access).</p>

		<h1><a name="SignupSheets-Howdotheywork"></a>How do they work?</h1>

		<p>When you log in to <?php echo LMS_DOMAIN; ?> you'll have the Sign-up Sheets block on the right hand side of their screen. This displays any impending
			signups
			you have and has three links: Available Openings, Sheet Admin, and My Signups. Clicking any of those links gets you into the tool, and the tool has
			a simple
			navigation to jump between sections.</p>

		<img src="../img/help/01_antd_my_page_with_block.png" alt="annotated screen shot of My page with sus block" />

		<img src="../img/help/02_antd_my_page_block_details.png" alt="annotated image of signup block" />

		<p><b>NOTE:</b> Sign-up sheets are a <?php echo LMS_DOMAIN; ?>-wide tool. That is, where a user is (on their my page, in a course, in an organization,
			or where
			ever else) doesn't affect which sheets they can get to or which up-coming signups they see. Whether a user can see and sign-up on a sheet is
			configured on a
			per-sheet basis by the creator of that sheet. E.g. if a user is given access to a sheet because they're in a BIO class, they'll still be able to see
			their
			sign-ups for that sheet listed when the sign-up sheet tool is displayed on the course page for their ENGL course.</p>

		<h5><a name="SignupSheets-ConfiguringtheBlockonCoursePages"></a>Configuring the Block on Course Pages</h5>

		<p>The block can also appear on course pages (and starting Fall 2010 it will do so by default). If you are teaching a course you can change the
			look-ahead (i.e.
			how soon a signup has to be to count as 'impending') by configuring the block.</p>
		<ol>
			<li>go to the course page</li>
			<li>click the 'Edit this page' button on the upper right <br /><img src="../img/help/03_turn_editing_mode_on_button.png" alt="" /></li>
			<li>on the Sign-up Sheets block, click the configure/edit icon <img src="../img/help/04_antd_block_in_editing_mode.png" alt="" /></li>
			<li>choose how far to look ahead, this Save Changes <img src="../img/help/05_configuring_block_look_ahead.png" alt="" /></li>
			<li>click the 'Turn editing off' button on the upper right <br /><img src="../img/help/06_turn_editing_mode_off_button.png" alt="" /></li>
		</ol>


		<h2><a name="SignupSheets-AvailableOpenings"></a>Available Openings</h2>

		<p>This is where you can see all the sheets that are available to you and can sign up for openings on those sheets. The first page lists the sheets.</p>

		<img src="../img/help/07_antd_available_openings_page.png" alt="annotated screenshot of av op page" />

		<p>Clicking on one of the sheets brings up a detailed page that shows more sheet information and all the openings. The openings are displayed in a
			calendar
			format, with small green markers indicating the days which have openings.</p>

		<img src="../img/help/08_antd_do_signup_page.png" alt="annotated screenshot of do signup page" />

		<p>Hovering the mouse over a green marker displays the exact time of the openings and shows the link to click to sign up. Clicking on the green marker
			shows to
			the right of the calendar a bit more detailed information about the openings on that day. Openings have a start time and an end time, and then a
			pair of
			numbers that shows how full that opening is. The first number is how many people are signed up for it, the second is the maximum number of signups
			that are
			allowed for that opening. The numbers are show in green if there is still space, and in red if it's full. You can click the remove icon to un-signup
			from an
			opening, though not for openings in the past. Likewise, you cannot sign up for an opening in the past.</p>

		<img src="../img/help/09_10_11_antd_do_signup_process.png" alt="annotated screenshot series of hovering and clicked on minitimes" />

		<p>By clicking the Openings as List tab above the calendar you can switch to a list-style view of the openings.<br />
			<img src="../img/help/12_antd_do_signup_list_view.png" alt="annotated screenshot of do signup page list view" />

		<h2><a name="SignupSheets-MySignups"></a>My Signups</h2>

		<p>This is where you can see everything you've signed up for AND all the sign-ups made by others on sheets that you own or manage. You can remove your
			own sign
			ups via this page, and also can remove others from openings on sheets you own or manage. To sign up for an opening you have to go to the Available
			Openings
			page (see above), and to sign someone else up for an opening on a sheet you own or manage you must go to the Sheet Admin page (see below).</p>

		<h3><a name="SignupSheets-IveSignedUpFor"></a>I've Signed Up For</h3>

		<img src="../img/help/13_antd_my_signups_I.png" alt="annotated screenshot of my signups" />

		<p>Text with a dashed underline will show more info if the mouse is hovered over it.</p>

		<img src="../img/help/13_antd_my_signups_I_dotted_hover.png" alt="annotated screenshot series of hover to see info" />

		<p>By default My Signups shows signups from the present and forward. Clicking the 'Show Past Signups' button displays historical information.</p>

		<img src="../img/help/14_antd_my_signups_I_past.png" alt="annotated screenshot series of clicking Show Past Signups" />

		<h3><a name="SignupSheets-SignupsForMe"></a>Sign-ups For Me</h3>

		<p>The second tab on this page shows sign-ups that other users have made on sheets that you own or manage.</p>

		<img src="../img/help/15_antd_my_signups_for_me.png" alt="annotated screenshot sign-ups for me" />

		<h2><a name="SignupSheets-SheetAdmin"></a>Sheet Admin</h2>

		<p>You can delete sheets and groups here (by clicking on the delete icon), can add new sheets and groups, and you can edit existing ones. This is also
			where you
			can manage sheets to which you've been given admin access, though you have more limited capabilities for those (e.g. you can't delete a sheet you
			don't
			own).</p>

		<img src="../img/help/16_antd_sheet_admin_main.png" alt="annotated screenshot of sheet admin" />

		<h3><a name="SignupSheets-SheetGroups"></a>Sheet Groups</h3>

		<p>Related sheets are organized into groups, kind of like putting a bunch of related documents into a folder. You always have a default sheet group.
			Additional
			groups may be created by clicking the 'Add a new group' link.</p>

		<p>A group is managed by clicking on its name. This lets you change the name, description, and settings for the group, and also lists the sheets in the
			group.</p>

		<img src="../img/help/17_antd_editing_a_sheet_group.png" alt="annotated screenshot of sheet group editing" />

		<p>The most important feature/setting of a group is the ability to set global signup limits across all sheets in the group. For example, the Psychology
			department is running a series of 5 labs, each of which will be run three times on three different dates. They want students to sign up for no more
			than
			three of those labs, but none of them more than once. They create 5 sheets called Lab1 through Lab5 and each sheet has three openings for the three
			different time that lab will be run. They set the limit on each sheet to 1 signup per person, so a user cannot sign up for the same lab three times.
			All
			those sheets are in the Psych Labs group, and that group is given a limit of three. Thus in addition to being limited to one signup per lab, the
			user is
			limited to three signups across all the labs.</p>

		<h3><a name="SignupSheets-Sheets"></a>Sheets</h3>

		<p>These are the heart of the system, the sheets on which users can sign up. New sheets are created by clicking the 'Add a new sheet to this group' link
			either
			directly on the Sheet Admin page, or from the Sheet Group page. Existing sheets may be edited by clicking their names.</p>

		<img src="../img/help/18_add_a_sheet_to_a_group_1.png" alt="annotated screenshots add a sheet to a group 1" />
		<img src="../img/help/19_add_a_sheet_to_a_group_2.png" alt="annotated screenshots add a sheet to a group 2" />

		<h4><a name="SignupSheets-CreatinganewSignupSheet"></a>Creating a new Sign-up Sheet</h4>

		<p>When you click 'Add a new sheet to this group' you start setting up a new sheet. To start you put in a name for the sheet, choose what group it will
			be in,
			and optionally put in a description or delete the place-holder text and thus leave the description blank.</p>

		<img src="../img/help/20_antd_new_sheet_creation.png" alt="annotated screenshot series of entering name, group, and description" />

		<p>Then you choose the time range for which the sheet will be active.</p>

		<img src="../img/help/21_new_sheet_time_range_1.png" alt="annotated screenshot series of choosing time range 1" />
		<img src="../img/help/22_new_sheet_time_range_2.png" alt="annotated screenshot series of choosing time range 2" />
		<img src="../img/help/23_new_sheet_time_range_3.png" alt="annotated screenshot series of choosing time range 3" />

		<p>Then put in any limits that you want - by default users can sign up any number of times on this sheet (but never more than once per opening).</p>

		<img src="../img/help/24_new_sheet_signup_limits_1.png" alt="annotated screenshot series of choosing limts 1" />
		<img src="../img/help/25_new_sheet_signup_limits_2.png" alt="annotated screenshot series of choosing limts 2" />

		<p>Finally, decide what alerts you want to receive. These are emails that you get about activity on the sheet. The 'signup or cancel' alert is an email
			every
			time a user signs up on this sheet. The 'upcomign signup' alert is an email no more than once a day about impending signups on this sheet. NOTE: the
			'upcoming signup' alerts from all your sheets are compiled into a single email - you won't get more than one email per day about upcoming signups on
			your
			sheets whether you have this turned on for one sheet or ten sheets.</p>

		<p>There are two sections to alerts: alerts for the owner, you, or whoever created the sheet, and alerts for admins, other users to which you have given
			admin
			access (see below under Sheet Editing for more info about that).</p>

		<img src="../img/help/26_new_sheet_alerts.png" alt="annotated screenshot series of alert options" />

		<p>Once you chosen you alert settings, click the Save button to create the sheet and go directly in to editing it.</p>

		<h4><a name="SignupSheets-EditingaSignupSheet"></a>Editing a Sign-up Sheet</h4>

		<p>When editing a sheet you can change anything you entered when creating the sheet, and you can do many other things as well. The page looks very
			similar to
			when creating a new sheet. but the left hand column has another tab, Sheet Access, where you can control who can sign up on this sheet and also can
			grant
			other users administrative privileges to this sheet. The calendar at the right shows the range in which the sheet is active, shows any existing
			openings
			(which you can edit - see below for details), and lets you add new openings. There's also a second tab for the right-hand column that lets you see
			the
			openings on this sheet as a list.</p>

		<img src="../img/help/27_antd_edit_sheet.png" alt="annotated screenshots of sheet editing" />

		<p>If you have openings and sign-ups for your sheet they'll be shown on both the calendar and list view.</p>

		<img src="../img/help/45_46_antd_edit_sheet_cal_list_with_signup.png" alt="annotated shot of an opening" />

		<h5><a name="SignupSheets-SheetAccess"></a>Sheet Access</h5>

		<p>The sheet access section gives you a wide range of options for specifying how others can interact with the sheet. Changes you make here are
			immediately saved
			- no need to go back to the Basic Sheet Info tab and click the save button.</p>

		<img src="../img/help/28_antd_edit_sheet_access.png" alt="annotated screenshots of sheet access" />
		<img src="../img/help/29_antd_edit_sheet_access_autosave.png" alt="annotated screenshots of sheet access autosave" />

		<p>First, you can determine whether users can see each other's sign-ups. By default signups are hidden. That is, a user might see that three other
			people have
			signed up for a given opening, but not who they are. You have the option here of allowing them to see who else has signed up for a given
			opening.</p>

		<p>Next, you set who can sign up for this sheet. There are many ways to do this, and a user dcan sign up if they meet ANY of teh conditions you speciyf
			here.</p>

		<ul>
			<li>People in these courses - This is a list of course in which you are enrolled, either as a student or a teacher. By checking the box next to a
				course you
				allow anyone enrolled in that course to sign up on this sheet.
			</li>
			<li>People in courses taught by - This is a list of everyone that teaches a course in <?php echo LMS_DOMAIN; ?>. By checking the box next to a
				person's name
				you allow anyone enrolled in a course that person teaches to sign up on this sheet.
			</li>
			<li>These people - This is an open text box where you type in the username (e.g. bviolet, bsv1, etc.) of people that will be able to sign up on this
				sheet.
				Separate username by commas or white space.
			</li>
			<li>People taking a course in - This is a list of all the departments. By checking the box next to a department you allow anyone enrolled in a
				course in
				that department to sign up on this sheet.
			</li>
			<li>People with a grad year of - This is a list of all the grad years of users in <?php echo LMS_DOMAIN; ?>. It includes not only the current
				student
				cohort, but also the grad year of any alumni in the system (e.g. if a Williams grad is later hired as a professor) and all the special codes for
				grad
				students, highschool students, etc.
				<ul>
					<li><em>NOTE: get interpretations&#33;</em></li>

				</ul>
			</li>
			<li>People who are a - This is a short list of very general attributes. This lets you open a sheet to anyone who's teaching a course
				in <?php echo LMS_DOMAIN; ?>, to anyone who's enrolled as a student in a course in <?php echo LMS_DOMAIN; ?>, or to everyone in the system.
			</li>
		</ul>


		<p>Finally, you can specify other users who have administrative access to manage this sheet. Admin users can do everything the owner can do, EXCEPT they
			can't
			change the group and they can't add or remove admin users.</p>

		<h5><a name="SignupSheets-CreatingOpenings"></a>Creating Openings</h5>

		<p>To create an opening on a day, click the + symbol in the lower left corner of that day on the calendar.</p>

		<img src="../img/help/30_antd_edit_sheet_calendar_closeup.png" alt="annotated screenshots of calendar and click" />

		<p>This pops up a window where you enter the information about the opening(s) you want to create. NOTE: You may have to tell your browser to allow
			popups
			from <?php echo LMS_DOMAIN; ?>.williams.edu.</p>

		<img src="../img/help/31_antd_create_opening_base.png" alt="annotated screenshots of new opening window, without optional fields" />
		<img src="../img/help/32_antd_create_opening_optional.png" alt="annotated screenshots of new opening window, with optional fields" />

		<p>At the top of this is a link to 'show optional fields'. Clicking that reveals additional things you can specify for your opening. You can give it a
			name, a
			description, notes that only you or an admin can see, and a location. The name, description, and location are sent to users in their reminder
			emails, in
			addition to the name and description of the sheet as a whole. E.g. you might have a sheet called Bio Field Trips, with openings called Forest,
			Aquarium, and
			Hospital.</p>

		<p>Below the optional fields you set the timing of the opening(s). There are two way to do this. The default, openings by time range, lets you put in a
			start
			time and an end time and the number of openings you want to make within that time range (evenly split - e.g. if you set the time from 1 PM to 2 PM
			and make
			3 openings, the system will create one opening from 1 to 1:20, another from 1:20 to 1:40, and a third from 1:40 to 2:00).</p>

		<img src="../img/help/33_34_antd_create_opening_modes.png" alt="annotated screenshots of both create openings modes" />

		<p>The other way, openings by duration, lets you specify a start time, a duration per opening, and a number of openings (e.g. if you specify starting at
			2:00,
			30 minutes each, 3 openings, the system will create one from 2 to 2:30, another from 2:30 to 3, and a third from 3 to 3:30).</p>

		<p>After that you set how many sign-ups are allowed; this is how many different users are allowed to signup for each opening created - no one is allowed
			to sign
			up for the same opening more than once.</p>

		<p>Finally, you can have the opening repeated either on given days of the week or on given days of the month.</p>

		<img src="../img/help/35_36_37_antd_create_opening_repeats.png" alt="annotated screenshots of weekly and monthly repeat modes" />

		<p>Choosing the 'Repeat on days of the week' option displays a list of toggle buttons, one per week day. Click a button to have the opening created that
			day.
			The 'Repeat on days of the month' option is similar, but it show 31 buttons instead of seven. In both cases the repetition happens until a certain
			date. By
			default that is the last day the sheet is active, but you can set it earlier if you wish. NOTE: this end date is used only when openings are being
			created&#33;
			If you create repeated openings and later extend the active date range of the sheet, the system will NOT create additional repeated openings out to
			that new
			date; you have to create additional openings manually when you extend the active dates of your sheet.</p>

		<p>Finally, clicks save will create the opening(s), close that window, AND save and re-load the sheet page.</p>

		<h5><a name="SignupSheets-DeletingOpenings"></a>Deleting Openings</h5>

		<p>To remove an opening just click on the red circled X next to the opening time. The system has a confirmation step to make sure you don't delete
			openings with
			an accidental click.</p>

		<h5><a name="SignupSheets-EditingOpeningsSigningOthersUpandEmailingSignedUpPeople"></a>Editing Openings, Signing Others Up, and Emailing Signed Up
			People</h5>

		<p>Once an opening is created you can edit any of its setting (for that opening only, not for all created at the same time), and the edit screen lets
			you do
			other things as well.</p>

		<img src="../img/help/38_antd_edit_sheet_openings_made.png" alt="annotated screenshots of edit links in cal-popup view" />
		<img src="../img/help/39_antd_edit_sheet_openings_made_list.png" alt="annotated screenshots of edit links in list views" />

		<p>All the optional fields are displayed when editing - opening name, opening description, admin note, and opening location. Each of these may be left
			blank or
			filled in as desired. You can also change the date of the opening, the start and/or end time, and the number of people that can sign up for this
			opening.</p>

		<img src="../img/help/41_antd_edit_opening.png" alt="annotated screenshot of edit opening window" />

		<p>On the top left is a print button - this lets you print out a clean version of the opening, with all the basic info and with an alphabetical list of
			all
			sign-ups.</p>

		<p>On the right-hand side of the window you can see the people that are currently signed up for this opening. By default they're in alphabetical order
			by last
			name, but the links at the top let you change the sorting to be in order of signup time (however, the printed version is always alphabetical). For
			each
			sign-up in the list&nbsp; you have the option of adding an admin note (e.g. "bringing the popcorn") which only the sheet owner and admins can see.
			You can
			remove users from this opening by clicking the signup icon next to their name. You can also sign users up for this opening by clicking the "Sign
			Someone Up"
			link at the top of the right-hand column.</p>

		<img src="../img/help/42_43_antd_edit_opening_signups.png" alt="annotated screenshots of signups list, of signing up a user" />

		<p>NOTE: when an owner or admin signs someone up for something the usual limit checks are skipped - an owner or admin can over-book an opening</p>

		<p>At the bottom of the window is the email tool. This lets you quickly and easily send an email to everyone who's signed up for an opening. Just enter
			the
			message you want to email, change the subject if you like, and click the Send button.</p>

		<img src="../img/help/44_antd_edit_opening_email.png" alt="annotated screenshot of message sending" />
		<?php
		// NOTE: END placeholder text from old Moodle Signup Sheets help file

		// end parent div
		echo "</div>";
	}

	require_once('../foot.php');
?>

<script type="text/javascript" src="<?php echo APP_ROOT_PATH; ?>/js/help.js"></script>
