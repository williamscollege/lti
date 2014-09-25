		/*
			Modifications started
			Author:		David Keiser-Clark (Williams College)
			Problem:	Our Tool Consumer (Canvas) does not publish error messages from Tool Provider (LTI app), but redirects to nest the Canvas course inside the iframe
			Solution:	Reset [$debugMode = TRUE] to force error messages to display on Tool Consumer
			Solution:	Add custom function to publish error and force exit
			Solution:	Add routine to catch 'Invalid consumer key.'
			File: 		LTI_Tool_Provider.php
			Modified: 	Lines 167, 376, 496
		*/
		// original: private $debugMode = FALSE;
		private $debugMode = TRUE;
		/*	Modifications completed */
		
---------------------------------
		
					/*
						Modifications started
						Author:		David Keiser-Clark (Williams College)
						Problem:	Our Tool Consumer (Canvas) does not publish error messages from Tool Provider (LTI app), but redirects to nest the Canvas course inside the iframe
						Solution:	Reset [$debugMode = TRUE] to force error messages to display on Tool Consumer
						Solution:	Add custom function to publish error and force exit
						Solution:	Add routine to catch 'Invalid consumer key.'
						File: 		LTI_Tool_Provider.php
						Modified: 	Lines 167, 376, 496
					*/
					if (isset($this->reason)) {
						echo "<div style=\"padding: 10px; background-color: #EEEEEE; border: 1px solid #373737;\">";
						echo "<h3>The requested LTI application has failed to load.</h3>";
						echo "Error message: &quot;" . $this->reason . "&quot;<br /><br />";
						echo "For help, please email <a href=\"mailto:itech@williams.edu?subject=Glow:%20The%20requested%20LTI%20application%20has%20failed%20to%20load\" target=\"_blank\" title=\"Williams College Instructional Technology\">itech@williams.edu</a> and include a screenshot of this page.<br />";
						echo "</div>";
						exit;
					}
					/*	Modifications completed */
					
---------------------------------					
					
				/*
					Modifications started
					Author:		David Keiser-Clark (Williams College)
					Problem:	Our Tool Consumer (Canvas) does not publish error messages from Tool Provider (LTI app), but redirects to nest the Canvas course inside the iframe
					Solution:	Reset [$debugMode = TRUE] to force error messages to display on Tool Consumer
					Solution:	Add custom function to publish error and force exit
					Solution:	Add routine to catch 'Invalid consumer key.'
					File: 		LTI_Tool_Provider.php
					Modified: 	Lines 167, 376, 496
				*/
				else {
					if (!$this->isOK) {
						$this->reason = 'Invalid consumer key.';
					}
				}
				/*	Modifications completed */
