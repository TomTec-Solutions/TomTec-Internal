<?php
  // TomTec Solutions
  require_once "assets/php/base.php";


  if (!Authentication::isLoggedIn() && Functions::getCurrentUrl() != "authenticate") {
    // take them back to the authenticate page if not logged in
    header("Location: authenticate");
    $_GET["url"] = "authenticate";
    $loginRend = new Renderer;
    $loginRend->setTemplateVariable("templateName", "authenticate.tmpl");
    $loginRend->setTemplateVariable("fileName", "authenticate");
    @include "assets/php/pages/" . Functions::getCurrentUrl(".php");
    die($loginRend->render("base.tmpl", true));
  }

  if (Authentication::isLoggedIn() && isset($_GET["logout"])) {
    Authentication::logout();
    header("Location: authenticate");
  }
  
  $rend = new Renderer;
  $rend->setTemplateVariable("siteName", TTS_Framework::SITE_NAME);
  $rend->setTemplateVariable("javascript", Functions::doesJavaScriptFileExist(Functions::getCurrentUrl(".js")));
  $rend->setTemplateVariable("stylesheet", Functions::doesStylesheetFileExist(Functions::getCurrentUrl(".css")));
  $rend->setTemplateVariable("pageNames", [
    "dashboard" => "Dashboard",
    "1" => [
      "Clients",
      "clientlist" => "<i class=\"fa fa-list\" aria-hidden=\"true\"></i> List Clients",
      "clientadd" => "<i class=\"fa fa-plus\" aria-hidden=\"true\"></i> Add New Client",
      "clientemails" => "<i class=\"fa fa-envelope\" aria-hidden=\"true\"></i> Client Email List",
    ],
    "2" => [
      "Invoicing",
      "invoicelist" => "<i class=\"fa fa-list\" aria-hidden=\"true\"></i> List Invoices",
      "invoiceadd" => "<i class=\"fa fa-plus\" aria-hidden=\"true\"></i> New Invoice",
    ],
  ]);
  $rend->setTemplateVariable("webPath", Functions::getWebPath());
  $rend->setTemplateVariable("isMobile", Functions::getDeviceType("mobile"));
  $rend->setTemplateVariable("fileName", Functions::getCurrentUrl());
  $rend->setTemplateVariable("templateName", Functions::getCurrentUrl(".tmpl"));
  $rend->setTemplateVariable("isLoggedIn", Authentication::isLoggedIn());
  if (Authentication::isLoggedIn()) {
    $rend->setTemplateVariable("UserData", new User(Authentication::getCurrentUserName()));
  }
  
  Functions::loadPagePhp(Functions::getCurrentUrl(".php"));
  $rend->render("base.tmpl");
  
