<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WS Client Feeder Mahasiswa Module
 * 
 * @author 		Yusuf Ayuba modified ahmadluky
 * @copyright   2015
 * @link        http://jago.link
 * @package     https://github.com/virbo/wsfeeder
 * 
*/

class Substansikuliah extends CI_Controller {

	//private $data;
	private $limit;
	private $filter;
	private $order;
	private $offset;
	private $table;
	private $table1;
	private $table2;
	private $template;
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
			$this->table = 'substansi_kuliah';
			$this->table1 = 'sms';
			$this->load->model('m_feeder','feeder');
			$this->load->helper('csv');
			$this->load->library('excel');
			$this->template = './template/matakuliah_template.xlsx';

			$config['upload_path'] = $this->config->item('upload_path');
			$config['allowed_types'] = $this->config->item('upload_tipe');
			$config['max_size'] = $this->config->item('upload_max_size');
			$config['encrypt_name'] = TRUE;

			$this->load->library('upload',$config);

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
	
	public function index()
	{
		$this->mk();
	}

	public function mk()
	{
		$temp_rec = $this->feeder->getrecord($this->session->userdata('token'), $this->table, $this->filter);
		$temp_sp = $this->session->userdata('id_sp');
		$temp_sms = $this->feeder->getrset($this->session->userdata('token'), $this->table1, 'id_sp=\'e1788280-0134-4b88-992b-d7184be667b9\'', $this->order, $this->limit, $this->offset);
		if (($temp_rec['error_desc']=='') && ($temp_sp=='') ){
			$this->session->set_flashdata('error','Kode PT Anda tidak ditemukan, silahkan masukkan kode PT anda dengan benar');
			redirect('welcome/setting');
		}
		$data['error_code'] = $temp_rec['error_code'];
		$data['error_desc'] = $temp_rec['error_desc'];
		$data['program_studi'] = $temp_sms['result'];
		$data['site_title'] = 'Daftar Substansi Kuliah';
		$data['title_page'] = 'Daftar Substansi Kuliah';
		$data['ket_page'] = 'Menampilkan dan mengelola data Substansi kuliah';
		$data['assign_js'] = 'js/substansikuliah_dt.js';
		$data['assign_modal'] = 'layout/modal_big_tpl.php';
		tampil('substansikuliah_view',$data);
	}

	public function uploadexcel()
	{
		$this->benchmark->mark('mulai');
		if (!$this->upload->do_upload()) {
			echo "<div class=\"bs-callout bs-callout-danger\">".$this->upload->display_errors()."</div>";
		} else {
			$mode = $this->input->post('mode');
			$file_data = $this->upload->data();
			$file_path = $this->config->item('upload_path').$file_data['file_name'];

			$objPHPExcel = PHPExcel_IOFactory::load($file_path);
			switch ($mode) {
				case 0:
					$objPHPExcel->setActiveSheetIndex(0);
					$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
					$jml_row = $objPHPExcel->getActiveSheet()->getHighestRow()-1;
					foreach ($cell_collection as $cell) {
						$column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
						$row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
						$data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
						
						if ($row == 1) {
							$header[$row][$column] = $data_value;
						} else {
							$arr_data[$row][$column] = $data_value;
						}
					}
					if ($arr_data) {
						$temp_data = array();
						$sukses_count = 0;
						$sukses_msg = '';
						$error_count = 0;
						$error_msg = array();
						$sukses_msg = array();
						foreach ($arr_data as $key => $value) 
						{
							$temp_data['id_sms'] = $value['B'];
							$temp_data['id_jenj_didik'] = $value['C'];
							$temp_data['kode_mk'] =$value['D'];
							$temp_data['nm_mk'] = $value['E'];
							$temp_data['jns_mk'] = $value['F'];
							$temp_data['kel_mk'] = $value['G'];
							$temp_data['sks_mk'] = $value['H'];
							$temp_data['sks_tm'] = $value['I'];
							$temp_data['sks_prak'] = $value['J'];
							$temp_data['sks_prak_lap'] = trim($value['K']);
							$temp_data['sks_sim'] = trim($value['L']);
							$temp_data['metode_pelaksanaan_kuliah'] = $value['M'];
							$temp_data['a_sap'] = $value['N'];
							$temp_data['a_silabus'] = $value['O'];
							$temp_data['a_bahan_ajar'] = $value['P'];
							$temp_data['acara_prak'] = $value['Q'];
							$temp_data['a_diktat'] = $value['R'];
							$temp_data['tgl_mulai_efektif'] = trim($value['S']);
							$temp_data['tgl_akhir_efektif'] = trim($value['T']);

							$temp_result = $this->feeder->insertrecord($this->session->userdata['token'], $this->table, $temp_data);
							if ($temp_result['result']) {
								if ($temp_result['result']['error_desc']==NULL) {
									++$sukses_count;
									$sukses_msg[] = "<h4>Sukses</h4> </strong>/<strong>".$value['D']."</strong> berhasil ditambahkan";
								} else {
									++$error_count;
									$error_msg[] = "<h4>Error ".$temp_result['result']['error_code']." (".$value['D']." / ".$value['E'].")</h4>".$temp_result['result']['error_desc'];
								}
							} else {
								echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error ".$temp_result['error_code']."</h4>".$temp_result['error_desc']."</div></div>";
								break;
							}
						}
						$this->benchmark->mark('selesai');
						$time_eks = $this->benchmark->elapsed_time('mulai', 'selesai');
						if ((!$sukses_count==0) || (!$error_count==0)) {
							echo "Waktu eksekusi ".$time_eks." detik<br />
									Results (total ".$jml_row." baris data):<br />
									<font color=\"#3c763d\">".$sukses_count." data baru berhasil ditambah</font>";
									if ($sukses_count!=0) {
										echo "<a data-toggle=\"collapse\" href=\"#cols_sukses\" aria-expanded=\"false\" aria-controls=\"cols_sukses\"> Detail</a><br />";
									} else { echo "<br />"; }
									echo "<div class=\"collapse\" id=\"cols_sukses\">";
											foreach ($sukses_msg as $pesan_sukses) {
												echo "<div class=\"bs-callout bs-callout-success\">".$pesan_sukses."</div><br />";
											}
									echo "</div>";

							echo "<font color=\"#ce4844\" >".$error_count." data tidak bisa ditambahkan </font>";
									if ($error_count!=0) {
										echo "<a data-toggle=\"collapse\" href=\"#cols_error\" aria-expanded=\"false\" aria-controls=\"cols_error\">Detail error</a>";
									}
									echo "<div class=\"collapse\" id=\"cols_error\">";
													foreach ($error_msg as $pesan) {
															echo "<div class=\"bs-callout bs-callout-danger\">".$pesan."</div><br />";
														}
											echo "</div>";
						}
					} else {
						echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>Tidak dapat mengekstrak file.. Silahkan dicoba kembali</div>";
					}
					break;
				case 1:
					echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>Option Ini masih dalam pengembangan</div>";
			}
		}
	}

	public function createexcel()
	{
		
		$this->benchmark->mark('mulai');
		$p = $this->input->get('p');
		if($p==""){
			echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4> Nama Prodi Harus Dipilih.</div>";
		} else {
			$prodi = explode('|', $this->input->get('p')); // 1.id_sms 2.id_jenjang pendidikan
			$temp_sp = $this->session->userdata('id_sp');
			
			if (!file_exists($this->template)) {
				echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>File template tidak tersedia.</div>";
			} else {
				//
				$data = array(
							array('id_sms' => $prodi[0],
								'id_jenjang_pendidikan' => $prodi[1],
								'kode_mk' => '',
								'mk_kuliah' => '',
								'jenis_mk' => '',
								'klompok_mk' => '',
								'sks_mk' => '',
								'sks_tm' => '',
								'sks_prak' => '',
								'sks_pl' => '',
								'sks_sim' => '',
								'metode_k' => '',
								'a_sap' => '',
								'a_sil' => '',
								'a_ba' => '',
								'a_prak' => '',
								'a_diklat' => '',
								'tgl_ef' => "'2015-13-24",
								'tgl_ak_ef' => "'2015-13-24")
						);
				$objPHPExcel = PHPExcel_IOFactory::load($this->template);

				//SET SHEET Mata Kuliah
				$objPHPExcel->setActiveSheetIndex(0);
				$baseRow = 3;
				foreach($data as $r => $dataRow) {
					$row = $baseRow + $r;
					$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $r+1)
										->setCellValue('B'.$row, $dataRow['id_sms'])
										->setCellValue('C'.$row, $dataRow['id_jenjang_pendidikan'])
										->setCellValue('D'.$row, $dataRow['kode_mk'])
										->setCellValue('E'.$row, $dataRow['mk_kuliah'])
										->setCellValue('F'.$row, $dataRow['jenis_mk'])
										->setCellValue('G'.$row, $dataRow['klompok_mk'])
										->setCellValue('H'.$row, $dataRow['sks_mk'])
										->setCellValue('I'.$row, $dataRow['sks_tm'])
										->setCellValue('J'.$row, $dataRow['sks_prak'])
										->setCellValue('K'.$row, $dataRow['sks_pl'])
										->setCellValue('L'.$row, $dataRow['sks_sim'])
										->setCellValue('M'.$row, $dataRow['metode_k'])
										->setCellValue('N'.$row, $dataRow['a_sap'])
										->setCellValue('O'.$row, $dataRow['a_sil'])
										->setCellValue('P'.$row, $dataRow['a_ba'])
										->setCellValue('Q'.$row, $dataRow['a_prak'])
										->setCellValue('R'.$row, $dataRow['a_diklat'])
										->setCellValue('S'.$row, $dataRow['tgl_ef'])
										->setCellValue('T'.$row, $dataRow['tgl_ak_ef']);
				}
				$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);

				$filename = time().'-template-matakuliah.xlsx';
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$temp_tulis = $objWriter->save('temps/'.$filename);
				$this->benchmark->mark('selesai');
				$time_eks = $this->benchmark->elapsed_time('mulai', 'selesai');
				if ($temp_tulis==NULL) {
					echo "<div class=\"bs-callout bs-callout-success\">
							File berhasil digenerate dalam waktu <strong>".$time_eks." detik</strong>. <br />Klik <a href=\"".base_url()."index.php/file/download/".$filename."\">disini</a> untuk download file
						</div>";
				} else {
					echo "<div class=\"bs-callout bs-callout-danger\">
							<h4>Error</h4>File tidak bisa digenerate. Folder 'temps' tidak ada atau tidak bisa ditulisi.
						</div>";
				}
			}
		}
	}

	public function jsonSK()
	{
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
			$temp_filter = "((id_jns_subst LIKE '%".$sSearch."%') OR (nm_subst LIKE '%".$sSearch."%') AND (p.id_sms=''))";
			$temp_rec = $this->feeder->getrset($this->session->userdata('token'),
												$this->table, 
												$temp_filter,
												'nm_subst DESC',
												$temp_limit,
												$temp_offset
												);
			$__total = $this->feeder->count_all($this->session->userdata('token'),$this->table,$temp_filter);
			$totalFiltered = $__total['result'];
		} else {
			$temp_filter = "";
			$temp_rec = $this->feeder->getrset($this->session->userdata('token'),
												$this->table, 
												$temp_filter,
												'nm_subst DESC',
												$temp_limit,
												$temp_offset
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

				$temps[] = $key['nm_subst'];

				$filter_sms = "id_jns_subst='".$key['id_jns_subst']."'";
				$temp_sms = $this->feeder->getrecord($this->session->userdata('token'),'jenis_subst',$filter_sms);
				$temps[] = $temp_sms['result']['nm_jns_subst'];

				$filter_sms = "id_sms='".$key['id_sms']."'";
				$temp_sms = $this->feeder->getrecord($this->session->userdata('token'),'sms',$filter_sms);
				$temps[]= $temp_sms['result']['nm_lemb']."'";
				

				$temps[] = $key['sks_mk'];
				$temps[] = $key['sks_tm'];
				$temps[] = $key['sks_prak'];
				$temps[] = $key['sks_prak_lap'];
				$temps[] = "";
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
}
