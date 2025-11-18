# TODO: Standardize PHP File Paths

- [x] Define ROOT_PATH in app/config.php
- [x] Create app/functions.php with consolidated functions from includes/functions.php and includes/helpers.php
- [x] Consolidate auth functions from includes/auth.php into app/auth.php
- [x] Update require statements in public/ files to use ROOT_PATH . '/app/auth.php'
- [x] Update require statements in admin/, school/, sponsor/ index.php to use ROOT_PATH . '/app/auth.php'
- [x] Update require statements in root index.php and register.php to use ROOT_PATH . '/app/auth.php' and '/app/functions.php'
- [x] Update includes/db_connect.php to use app/config.php (but since deprecating, move db logic to app/config.php if needed)
- [x] Deprecate includes/ by removing or commenting references
- [x] Test key files for correct includes
