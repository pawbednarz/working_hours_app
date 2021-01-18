<?php

use core\App;
use core\Utils;

App::getRouter()->setDefaultRoute('dashboard'); #default action
App::getRouter()->setLoginRoute('login'); #action to forward if no permissions

Utils::addRoute('addUser', 'UserController');

// login / logout routes
Utils::addRoute('login', 'LoginController');
Utils::addRoute('logout', 'LoginController');

// entry routes
Utils::addRoute('dashboard', 'EntryController', ["admin", "user"]);
Utils::addRoute('showEntries', 'EntryController', "user");
Utils::addRoute('addEntry', 'EntryController', "user");
//Utils::addRoute('editEntry', 'EntryController', "user");
Utils::addRoute('deleteEntry', 'EntryController', "user");

//// recepient routes
Utils::addRoute('showRecipients', 'RecipientController', "user");
Utils::addRoute('addRecipient', 'RecipientController', "user");
//Utils::addRoute('editRecipient', 'RecipientController', "user");
Utils::addRoute('deleteRecipient', 'RecipientController', "user");

// email routes
Utils::addRoute('showEmailTemplates', 'EmailTemplateController', "user");
Utils::addRoute('addEmailTemplate', 'EmailTemplateController', "user");
//Utils::addRoute('editEmailTemplate', 'EmailTemplateController', "user");
Utils::addRoute('deleteEmailTemplate', 'EmailTemplateController', "user");

//// report routes
//Utils::addRoute('showReports', 'ReportController', "user");
//Utils::addRoute('generateReport', 'ReportController', "user");
//Utils::addRoute('deleteReport', 'ReportController', "user");
//
//// send email routes
//Utils::addRoute('sendEmail', 'EmailSendController', "user");
//Utils::addRoute('sendReport', 'EmailSendController', "user");
//
//Utils::addRoute('showEmails', 'EmailSendController', "user");
//Utils::addRoute('showEmail', 'EmailSendController', "user");
//Utils::addRoute('deleteEmail', 'EmailSendController', "user");
//
// administrative routes
Utils::addRoute('adminDashboard', 'AdminController', "admin");
//
// user routes (only for administrator)
//Utils::addRoute('addUser', 'UserController', "admin");
//Utils::addRoute('editUser', 'UserController', "admin");
//Utils::addRoute('deleteUser', 'UserController', "admin");
