<?php

class Invoice extends TTS_Framework {

  public $id;
  public $client;
  public $issue_date;
  public $due_date;
  public $technician;
  public $price;
  public $description;
  public $state;

  public function __construct($id) {
    if (self::exists($id)) {
      $invoice = $this->getInvoiceById($id);
      $this->id = $id;
      $this->client = $invoice["client"];
      $this->issue_date = $invoice["issue_date"];
      $this->due_date = $invoice["due_date"];
      $this->technician = $invoice["technician"];
      $this->price = $invoice["total_price"];
      $this->work_description = $invoice["work_description"];
      $this->state = $invoice["state"];
    } else {
      throw new Error("Invoice ID #" . $id . " does not exist.");
    }
  }

  public static function exists($id) {
    $db = new Database;
    return $db->sIsMember("internal:invoices", $id);
  }

  public static function getNewInvoiceId() {
    $db = new Database;
    $highest = 10100;
    foreach ($db->sMembers("internal:invoices") as $id) {
      if (intval($id) > $highest) {
        $highest = intval($id);
      }
    }
    return ($highest + 1);
  }

  public static function getLatestInvoiceId() {
    return (self::getNewInvoiceId() - 1);
  }

  private function getInvoiceById($id) {
    $db = new Database;
    return $db->hGetAll("internal:invoices:" . $id);
  }

  public function delete() {
    $db = new Database;
    $db->sRemove("internal:invoices", $this->id);
    return $db->delete("internal:invoices:" . $this->id);
  }

  public static function add($data, $id) {
    $db = new Database;
    if (!Invoice::exists($id)) {
      $db->sAdd("internal:invoices", $id);
      return $db->hMSet(("internal:invoices:" . $id), $data);
    } else {
      throw new Error("Invoice ID #" . $id . " already exists.");
    }
  }

  public static function allInvoices() {
    $db = new Database;
    $all_invoices = array();
    if (file_exists("all_invoices.cache")) {
      return unserialize(file_get_contents("all_invoices.cache"));
    } else {
      try {
        foreach ($db->sMembers("internal:invoices") as $id) {
          $all_invoices[$id] = new Invoice($id);
        }
        file_put_contents("all_invoices.cache", serialize($all_invoices), LOCK_EX);
        return $all_invoices;
      } catch (Throwable $e) {
        return $e->getMessage();
      }
    }
  }

  public function update($data) {
    $db = new Database;
    return $db->hMSet(("internal:invoices:" . $this->id), $data);
  }

  public static function getFinalEarningsPrice() {
    $total = 0;
    try {
      foreach (Invoice::allInvoices() as $invoice) {
        if ($invoice->state == "Paid") {
          $total += floatval($invoice->price);
        }
      }
      return $total;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }
  
  public static function getFinalEarningsPriceForYear($year) {
    $total = 0;
    try {
      foreach (Invoice::allInvoices() as $invoice) {
        if ($invoice->issue_date > mktime(0, 0, 0, 1, 1, intval($year)) && $invoice->issue_date < mktime(0, 0, 0, 1, 1, (intval($year) + 1))) {
          $total += floatval($invoice->price);
        }
      }
      return $total;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }
  
  public static function getFinalEarningsPriceForMonthsInYear($year) {
    $months = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0];
    try {
      foreach (Invoice::allInvoices() as $invoice) {
        if ($invoice->issue_date > mktime(0, 0, 0, 1, 1, intval($year)) && $invoice->issue_date < mktime(0, 0, 0, 1, 1, (intval($year) + 1))) {
          $months[date("n", $invoice->issue_date)] += floatval($invoice->price);
        }
      }
      return $months;
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }
  
  public static function getAllYears() {
    $years = [];
    try {
      foreach (Invoice::allInvoices() as $invoice) {
        $years[] = date("Y", $invoice->issue_date);
      }
      return array_values(array_unique($years, SORT_NUMERIC));
    } catch (Throwable $e) {
      return $e->getMessage();
    }
  }
}
  
  // remove cache file
  register_shutdown_function(function () {
    $invoices_cache = "/var/www/tomtecinternal/all_invoices.cache";
    if (file_exists($invoices_cache)) {
      unlink($invoices_cache);
    }
  });
