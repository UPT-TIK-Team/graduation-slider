<?php

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Config\Services;
use App\Models\WisudawanModel;

class Wisudawan extends BaseController
{
  protected $wisuda;

  public function __construct()
  {
    session();
    $this->wisuda = new WisudawanModel();
  }

  public function index()
  {
    $data = ['wisudawan' => $this->wisuda->findAll(), 'validation' => Services::validation()];
    return view('wisudawan/index', $data);
  }

  public function upload_excel()
  {
    session();
    // Handle if request method is GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      return view('wisudawan/upload_excel');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = ['validation' => Services::validation()];
      return view('wisudawan/upload_excel');
    }
    // Handle if file mime_in is excel
    if (!$this->validate(['excel-file' => 'uploaded[excel-file]|mime_in[excel-file,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet]'])) {
      $this->session->set_flashdata('error', true);
      return redirect()->to(base_url('/wisudawan/upload_excel'))->withInput();
    }
    $excel_file = $this->request->getFile('excel-file');
    $reader = new Xlsx();
    $spreadsheet = $reader->setReadEmptyCells(false)->load($excel_file);
    $data = $spreadsheet->getActiveSheet()->toArray();
    foreach ($data as $i => $row) {
      if ($i === 0) continue;
      $new_data = ['npm' => $row[0], 'nama' => $row[1]];
      $this->wisuda->insert($new_data);
    }
    return redirect()->to(base_url());
  }
}
