Mitihub Project Structure

project/
├── admin/
│   ├── index.php
│   ├── dashboard.php
│   ├── profile.php
│   └── functions.php
│
├── school/
│   ├── index.php
│   ├── dashboard.php
│   ├── students.php
│   └── functions.php
│
├── sponsor/
│   ├── index.php
│   ├── dashboard.php
│   ├── payments.php
│   └── functions.php
│
├── includes/
│   ├── db_connect.php
│   ├── auth.php
│   └── helpers.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── config.php
├── index.php
└── README.md

Notes
- This repository already contains an earlier scaffold; this README documents the module-based structure you requested. The files referenced below will be created.

Setup
- Ensure you have a MySQL database created and configure DB credentials in config.php.
- Place shared logic in includes/*.php and module-specific logic in each module's functions.php.
