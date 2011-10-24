<?php

require( '../includes/config.php' );

ae_Permissions::Logout();

header( 'Location: index.php?success=logout' );
