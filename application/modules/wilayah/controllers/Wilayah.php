<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wilayah extends CI_Controller {

	//private $data;
	private $limit;
	private $filter;
	private $order;
	private $offset;
	private $table;
	private $dir_ws;
	private $host_ws;
	private $port_ws;


	public function __construct()
	{
		parent::__construct();
		if (!$this->session->userdata('login')) {
			redirect('ws');
		} else {
			$this->limit = $this->config->item('limit');
			$this->filter = $this->config->item('filter');
			$this->order = $this->config->item('order');
			$this->offset = $this->config->item('offset');
			$this->table = 'wilayah';
			$this->load->model('m_feeder','feeder');
			$this->load->helper('csv');
			$this->load->library('excel');
			$this->template = './template/wilayah_template.xlsx';

			$temp_setting = read_file('setting.ini');
			$pecah = explode('#', $temp_setting);
			$this->dir_ws = $pecah[1];
			$this->host_ws = parse_url($this->dir_ws, PHP_URL_HOST);
			$this->port_ws = parse_url($this->dir_ws, PHP_URL_PORT);
			$ping = ping($this->host_ws,$this->port_ws);
			if (!$ping) {
				show_error('Error, Tidak bisa menghubungi server. Silahkan check koneksi LAN atau koneksi Internet Anda');
			}
		}
	}
	public function index(){
		$this->wilayah();
	}
	public function wilayah(){
		$temp_rec = $this->feeder->getrecord($this->session->userdata('token'), $this->table, $this->filter);
		$temp_sp = $this->session->userdata('id_sp');
		if (($temp_rec['error_desc']=='') && ($temp_sp=='') ){
			$this->session->set_flashdata('error','Kode PT Anda tidak ditemukan, silahkan masukkan kode PT anda dengan benar');
			redirect('welcome/setting');
		}
		$data['error_code'] = $temp_rec['error_code'];
		$data['error_desc'] = $temp_rec['error_desc'];
		$data['site_title'] = 'Daftar Wilayah';
		$data['title_page'] = 'Daftar Wilayah';
		$data['assign_js'] = 'js/wilayah_dt.js';
		$data['assign_modal'] = '';
		tampil('wilayah_view',$data);
	}
	public function jsonWil(){
		$search = $this->input->post('search');
		$sSearch = trim($search['value']);
		$orders = $this->input->post('order');
		$iStart = $this->input->post('start');
		$iLength = $this->input->post('length');
		$temp_limit = $iLength;
		$temp_offset = $iStart?$iStart : 0;
		$temp_total = $this->feeder->count_all($this->session->userdata('token'),$this->table,$this->filter);
		$totalData = $temp_total['result'];
		$totalFiltered = $totalData;
		if (!empty($sSearch)) {
			$temp_filter = "nm_wil like '%".$sSearch."%'";
			$temp_rec = $this->feeder->getrset(
				$this->session->userdata('token'),
				$this->table, $temp_filter,'',
				$temp_limit,$temp_offset
						);
			$__total = $this->feeder->count_all($this->session->userdata('token'),$this->table,$temp_filter);
			$totalFiltered = $__total['result'];
		} else {
			$temp_rec = $this->feeder->getrset(
				$this->session->userdata('token'),
				$this->table, $this->filter,'',
				$temp_limit,$temp_offset
			);
		}
		$temp_error_code = $temp_rec['error_code'];
		$temp_error_desc = $temp_rec['error_desc'];
		if (($temp_error_code==0) && ($temp_error_desc=='')) {
			$temp_data = array();
			$i=0;
			foreach ($temp_rec['result'] as $key) {
				$temps = array();
				$temps[] = ++$i+$temp_offset;
				$temps[] = $key['id_wil'];
				$temps[] = $key['nm_wil'];
				$temp_data[] = $temps;
			}
			$temp_output = array(
				'draw' => intval($this->input->get('draw')),
				'recordsTotal' => intval( $totalData ),
				'recordsFiltered' => intval( $totalFiltered ),
				'data' => $temp_data
			);
			echo json_encode($temp_output);
		}
	}
	public function createexcel(){
		$this->benchmark->mark('mulai');
		$p = $this->input->get('p');
		if (!file_exists($this->template)) {
			echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>File template tidak tersedia.</div>";
		} else {
			$temp_rec = $this->feeder->getrset(
									$this->session->userdata('token'), $this->table, 
									$this->filter,'',
									$this->limit,$this->offset
								  );
			$temp_error_code = $temp_rec['error_code'];
			$temp_error_desc = $temp_rec['error_desc'];
			$data0 = array();
			if (($temp_error_code==0) && ($temp_error_desc=='')) {
				$temp_data = array();
				foreach ($temp_rec['result'] as $key) {
					$temps = array();
					$temps[] = $key['id_wil'];
					$temps[] = $key['nm_wil'];
					$data0[] = $temps;
				}
			}			

			$objPHPExcel = PHPExcel_IOFactory::load($this->template);
			$objPHPExcel->setActiveSheetIndex(0);
			$baseRow = 3;
			foreach($data0 as $r => $dataRow) {
				$row = $baseRow + $r;
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $dataRow[0])
											  ->setCellValue('B'.$row, $dataRow[1]);
			}
			$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);
			$filename = time().'-wilayah_template.xlsx';
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$temp_tulis = $objWriter->save('temps/'.$filename);
			$this->benchmark->mark('selesai');
			$time_eks = $this->benchmark->elapsed_time('mulai', 'selesai');
			if ($temp_tulis==NULL) {
				echo "<div class=\"bs-callout bs-callout-success\">
						File berhasil digenerate dalam waktu <strong>".$time_eks." detik</strong>. 
						<br />Klik <a href=\"".base_url()."index.php/file/download/".$filename."\">disini</a> untuk download file
					</div>";
			} else {
				echo "<div class=\"bs-callout bs-callout-danger\">
						<h4>Error</h4>File tidak bisa digenerate. Folder 'temps' tidak ada atau tidak bisa ditulisi.
					</div>";
			}
		}
	}
}
