#!/bin/sh
############################################################
# Copyright (C) 2014 Williams College All Rights Reserved
############################################################
#
# PROGRAM:    acme.sh
#  AUTHOR:    David
#  E-MAIL:    David
# CREATED:    2014-01-01
# RUNS ON:    server_name
# SUMMARY:
#




$ECHO "shell begin first php script"
php record_status_stage_1.php
$ECHO "shell end first php script"

$ECHO "shell begin second php script"
php record_status_stage_2.php
$ECHO "shell end second php script"

$ECHO  "shell is all done"