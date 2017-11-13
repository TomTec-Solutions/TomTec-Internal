<?php
  require_once dirname(dirname(__FILE__)) . "/base.php";
  
  try {
    $user = new User(Authentication::getCurrentUserName());
    $invoices = Invoice::allInvoices();
    $clients = Client::allClients();
  } catch (Throwable $e) {
    print_r($e->getMessage());
    $rend->setAlert("warning", "Data retrieval failed. Error: " . $e->getMessage());
  }
  
  $owed = 0;
  $pastdue = 0;
  $time = time();
  $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  $statescount = [
    "Awaiting Payment" => 0,
    "Overdue"          => 0,
    "Paid"             => 0,
  ];
  foreach ($invoices as $invoice) {
    if ($time > $invoice->due_date && $invoice->state == "Awaiting Payment") {
      $pastdue += 1;
    }
    if ($invoice->state != "Paid") {
      $owed += $invoice->price;
    }
    $statescount[$invoice->state] += 1;
    
  }
  
  foreach (array_reverse(Invoice::getAllYears()) as $year) {
    if ($year != "1970") { // exclude test invoices
      $earnings[$year] = Invoice::getFinalEarningsPriceForMonthsInYear($year);
      $earnings[$year]["total"] = Invoice::getFinalEarningsPriceForYear($year);
    }
  }
  
  try {
    $smsCredits = SMS::getCreditBalance(); // forgot that this function can throw an exception
    $rend->setTemplateVariable("SmsCreditsMapped", Functions::map($smsCredits, 0, 1000, 0, 100));
  } catch (Throwable $error) {
    $smsCredits = $error;
  } finally {
    $rend->setTemplateVariable("SmsCredits", $smsCredits);
  }
  
  $rend->setTemplateVariable("Months", $months);
  $rend->setTemplateVariable("TotalOwed", number_format(money_format("%.2n", $owed), 2));
  $rend->setTemplateVariable("TotalEarnings", number_format(money_format("%.2n", Invoice::getFinalEarningsPrice()), 2));
  $rend->setTemplateVariable("AllEarnings", $earnings);
  $rend->setTemplateVariable("CurrentYearEarnings", number_format(money_format("%.2n", Invoice::getFinalEarningsPriceForYear(date("Y"))), 2));
  $rend->setTemplateVariable("PastDue", $pastdue);
  $rend->setTemplateVariable("InvoicesCount", count($invoices));
  $rend->setTemplateVariable("ClientsCount", count($clients));
  $rend->setTemplateVariable("FirstName", explode(" ", $user->fullname)[0]);
  $rend->setTemplateVariable("StatesCount", $statescount);
  $rend->setTemplateVariable("Notepad", htmlspecialchars_decode($user->notepad));

  
  