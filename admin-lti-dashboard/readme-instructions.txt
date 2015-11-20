-----------------------------------
Instructions to "Bulk Push Avatar Image Files" on Instructure Canvas using an Amazon cloud hosted environment for storage
	- This script is optimized to run slowly so as to not exceed Canvas stated limits on API requests per hour (3000). 
	- It should run until all users in the provided array have been completed.
	- It includes a check so as to not replace any valid pre-existing images

-----------------------------------

Preparation:
	- Download Live Canvas Users list: Settings/Reports/Provisioning --> Users (select Term)
		C:\..\PROJECTS\Canvas\live-reports-downloaded
	- config.php
		Update Canvas destination (i.e. williams.test.instructure.com OR williams.instructure.com)
	- update_these_users.php
		Copy desired Canvas User_ID values from provisioned list into "update_these_users.php" array
		NOTE: Since this will be run each semester, sort by Canvas User_ID and select only > than greatest ID value submitted last semester
	- fetch images of new Canvas users (from step above) using TOAD client and SQL to filter by username (LOGIN_ID)
		- Export BLOBS (right click)
		- Export this column: EMPLOYEE_PHOTO
		- Export Path: C:\transfer-files\OneCard-Images
		- Export to files named for the value in this column: LOGIN_ID
		- File Extension: .jpg
		- OK --> this will save images to local path on my computer
		- Sort physical images by size. delete any empty images
		- Get list of filenames (cd to that directory; dir > file.txt)
		- Get list of usernames lacking an image
			- Use text file to find Canvas usernames missing images
				- From full list of Canvas usernames, replace any existing ones with '' to leave only the missing ones
					- Regex (select options: Case, Words)
					- Search:	08hml|09akq|aac6|...
					- Replace:
		# SKIP THIS STEP:- Create missing "*.jpg" placeholder images for any users
		#	- Use Excel to create a list of DOS copy statements for each missing user image:
		#		copy C:\transfer-files\logs1\missing\missing.jpg C:\transfer-files\logs1\missing\08hml.jpg
		#		copy C:\transfer-files\logs1\missing\missing.jpg C:\transfer-files\logs1\missing\09akq.jpg
		#		copy C:\transfer-files\logs1\missing\missing.jpg C:\transfer-files\logs1\missing\aac6.jpg
		#	- Run statements in Windows Command Prompt
		- Rename image files (from username.jpg to CanvasID.jpg)
			- Use this file to quickly create perl script:
		 		"C:\xampp\htdocs\python\canvas-api-scripts\rename-images-username-to-canvasid"
		- PSCP images to Unix server so they are publicly accessible for AWS upload
			pscp -r *.jpg dwk2@unix.williams.edu:/web/oit/temp_avatar_images

	- Run script
		https://itech-sandbox.williams.edu/apps/canvas-api-scripts/bulk-push-avatar-image-files/index.php
		Done!


Sample Output:
	------------------------------
	FINAL STATUS REPORT
	
	Project: 'Bulk Push Avatar Image Files'
	Date started: 2014-10-29 11:51:33
	Date ended: 2014-10-29 11:51:37
	Duration (hh:mm:ss): 00:00:04
	Curl API Requests: 44
	User Count: 4
	Users skipped (error): 1
	Users skipped (pre-existing avatar): 2
	Users upload_status = 'pending': 0 (waiting, system busy)
	Users upload_status = 'ready': 4 (should match 'confirmed')
	Users upload_status = 'confirmed': 4 (files uploaded)
	Archived file: 20141029-115133-log-report.txt
	
	------------------------------
