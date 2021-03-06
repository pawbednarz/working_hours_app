<?php

use core\App;
use core\Utils;

App::getRouter()->setDefaultRoute('dashboard'); #default action
App::getRouter()->setLoginRoute('login'); #action to forward if no permissions

// login / logout routes
Utils::addRoute('login', 'LoginController');
Utils::addRoute('logout', 'LoginController', ["admin", "user"]);

// entry routes
Utils::addRoute('dashboard', 'EntryController', ["admin", "user"]);
Utils::addRoute('showEntries', 'EntryController', "user");
Utils::addRoute('addEntry', 'EntryController', "user");
Utils::addRoute('editEntry', 'EntryController', "user");
Utils::addRoute('deleteEntry', 'EntryController', "user");
Utils::addRoute('showEntriesForMonth', 'EntryController', "user");

// recepient routes
Utils::addRoute('showRecipients', 'RecipientController', "user");
Utils::addRoute('addRecipient', 'RecipientController', "user");
Utils::addRoute('editRecipient', 'RecipientController', "user");
Utils::addRoute('deleteRecipient', 'RecipientController', "user");

// email routes
Utils::addRoute('showEmailTemplates', 'EmailTemplateController', "user");
Utils::addRoute('addEmailTemplate', 'EmailTemplateController', "user");
Utils::addRoute('editEmailTemplate', 'EmailTemplateController', "user");
Utils::addRoute('deleteEmailTemplate', 'EmailTemplateController', "user");

// report routes
Utils::addRoute('showReports', 'ReportController', "user");
Utils::addRoute('generateReport', 'ReportController', "user");
Utils::addRoute('downloadReport', 'ReportController', "user");
Utils::addRoute('deleteReport', 'ReportController', "user");

// send email routes
Utils::addRoute('sendEmail', 'EmailSendController', "user");
Utils::addRoute('showEmails', 'EmailSendController', "user");
Utils::addRoute('sendEmaill', 'EmailSendController', "user");
Utils::addRoute('showEmail', 'EmailSendController', "user");
Utils::addRoute('deleteEmail', 'EmailSendController', "user");

// administrative routes
Utils::addRoute('adminDashboard', 'AdminController', "admin");

// user routes (only for administrator)
Utils::addRoute('addUser', 'AdminController', "admin");
Utils::addRoute('editUser', 'AdminController', "admin");

// AJAX endpoints
Utils::addRoute("getEntriesAjax", "EntryController", "user");
Utils::addRoute("getEntriesAjaxPage", "EntryController", "user");
