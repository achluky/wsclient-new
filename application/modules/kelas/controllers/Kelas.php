<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WS Client Feeder Kelas Module
 * 
 * @author 		Yusuf Ayuba modified by @ahmadluky
 * @copyright   2015
 * @link        http://jago.link
 * @package     https://github.com/virbo/wsfeeder
 * 
*/

class Kelas extends CI_Controller {

	//private $data;
	private $limit;
	private $filter;
	private $order;
	private $offset;
	private $table1;
	private $table2;
	private $table3;
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
			$this->table1 = 'kelas_kuliah.raw';
			$this->table2 = 'nilai.raw';
			$this->table3 = 'ajar_dosen.raw';
			$this->load->model('m_feeder','feeder');
			$this->load->helper('csv');
			$this->load->library('excel');
			$this->template = './template/kelas_template.xlsx';

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
		$this->kelas();
	}

	public function kelas()
	{
		$temp_rec = $this->feeder->getrecord($this->session->userdata('token'), $this->table1, $this->filter);
		$temp_sms = $this->feeder->getrset($this->session->userdata('token'), 'sms', 'id_sp=\'e1788280-0134-4b88-992b-d7184be667b9\'', $this->order, $this->limit, $this->offset);
		$temp_sp = $this->session->userdata('id_sp');
		if (($temp_rec['error_desc']=='') && ($temp_sp=='') ){
			$this->session->set_flashdata('error','Kode PT Anda tidak ditemukan, silahkan masukkan kode PT anda dengan benar');
			redirect('welcome/setting');
		}
		
		$data['error_code'] = $temp_rec['error_code'];
		$data['error_desc'] = $temp_rec['error_desc'];
		$data['program_studi'] = $temp_sms['result'];
		$data['site_title'] = 'Kelas/Nilai Perkuliahan';
		$data['title_page'] = 'Kelas/Nilai Perkuliahan';
		$data['ket_page'] = 'Menyimpan jadwal/nilai perkuliahan yang di buka, dosen pengajar, serta peserta kelas / KRS mahasiswa setiap periode';
		$data['assign_js'] = 'js/kelas_dt.js';
		$data['assign_modal'] = '';
		tampil('kelas_view',$data);
	}

	public function nilai($id_kls='')
	{
		if ($id_kls!='') {
			echo($id_kls);
		} else {
			redirect('kelas');
		}
	}

	public function createexcel()
	{
		$this->benchmark->mark('mulai');
		$p = $this->input->get('p');
		if($p==""){
			echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4> Nama Prodi Harus Dipilih.</div>";
		} else {

			$prodi = explode('|', $this->input->get('p')); 

			if (!file_exists($this->template)) {
				echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>File template tidak tersedia.</div>";
			} else {
				$data1 = array(array('kode_mk' => 'WS123',
							'mata_kuliah' => 'Mata Kuliah 1',
							'semester' => '20142',
							'kelas' => '01',
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						),
					  array('kode_mk' => 'WS456',
							'mata_kuliah' => 'Mata Kuliah 2',
							'semester' => '20142',
							'kelas' => '02',
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						),
					  array('kode_mk' => 'WS789',
							'mata_kuliah' => 'Mata Kuliah 3',
							'semester' => '20142',
							'kelas' => '01',
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						)
					);
				$data2 = array(array('nim' => '12345',
							'nama' => 'Mahasiswa 1',
							'kode_mk' => 'WS123',
							'mata_kuliah' => 'Mata Kuliah 1',
							'kelas' => '01',
							'semester' => '20142',
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						),
					  array('nim' => '45678',
							'nama' => 'Mahasiswa 2',
							'kode_mk' => 'WS456',
							'mata_kuliah' => 'Mata Kuliah 2',
							'kelas' => '02',
							'semester' => '20142',
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						),
					  array('nim' => '6789',
							'nama' => 'Mahasiswa 3',
							'kode_mk' => 'WS789',
							'mata_kuliah' => 'Mata Kuliah 3',
							'kelas' => '01',
							'semester' => '20142',
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						)
					);
				$data3 = array(array('nidn' => '12345',
							'dosen' => 'Dosen 1',
							'kode_mk' => 'WS123',
							'mata_kuliah' => 'Mata Kuliah 1',
							'kelas' => '01',
							'rencana_tm' => '16',
							'real_tm' => '16',
							'semester' => '20142',
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						),
					  array('nidn' => '34567',
							'dosen' => 'Dosen 2',
							'kode_mk' => 'WS456',
							'mata_kuliah' => 'Mata Kuliah 2',
							'kelas' => '02',
							'rencana_tm' => '16',
							'real_tm' => '16',
							'semester' => '20142',
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						),
					  array('nidn' => '56789',
							'dosen' => 'Dosen 3',
							'kode_mk' => 'WS789',
							'mata_kuliah' => 'Mata Kuliah 3',
							'kelas' => '01',
							'rencana_tm' => '16',
							'real_tm' => '16',
							'semester' => '20142',
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						)
					);
				$data4 = array(array('nim' => '12345',
							'nama' => 'Mahasiswa 1',
							'kode_mk' => 'WS123',
							'mata_kuliah' => 'Mata Kuliah 1',
							'semester' => '20142',
							'kelas' => '01',
							'nilai_angka' => 80,
							'nilai_huruf' => 'A',
							'nilai_indeks' => 4,
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						),
					  array('nim' => '23456',
							'nama' => 'Mahasiswa 2',
							'kode_mk' => 'WS456',
							'mata_kuliah' => 'Mata Kuliah 2',
							'semester' => '20142',
							'kelas' => '01',
							'nilai_angka' => 60,
							'nilai_huruf' => 'C',
							'nilai_indeks' => 2,
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						),
					  array('nim' => '34567',
							'nama' => 'Mahasiswa 3',
							'kode_mk' => 'WS789',
							'mata_kuliah' => 'Mata Kuliah 3',
							'semester' => '20142',
							'kelas' => '01',
							'nilai_angka' => 75,
							'nilai_huruf' => 'B',
							'nilai_indeks' => 3,
							'id_sms' => $prodi[0],
							'jenjang_pendidikan' => $prodi[1]
						)
					);
				$objPHPExcel = PHPExcel_IOFactory::load($this->template);
				//SET SHEET Kelas
				$objPHPExcel->setActiveSheetIndex(0);
				$baseRow = 3;
				foreach($data1 as $r => $dataRow) {
					$row = $baseRow + $r;
					$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $r+1)
										->setCellValue('B'.$row, $dataRow['kode_mk'])
										->setCellValue('C'.$row, $dataRow['mata_kuliah'])
										->setCellValue('D'.$row, $dataRow['semester'])
										->setCellValue('E'.$row, $dataRow['kelas'])
										->setCellValue('F'.$row, $dataRow['id_sms'])
										->setCellValue('G'.$row, $dataRow['jenjang_pendidikan']);
				}
				$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);

				//SET SHEET krs
				$objPHPExcel->setActiveSheetIndex(1);
				$baseRow2 = 3;
				foreach($data2 as $r2 => $dataRow2) {
					$row2 = $baseRow2 + $r2;
					$objPHPExcel->getActiveSheet()->insertNewRowBefore($row2,1);
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$row2, $r2+1)
										->setCellValue('B'.$row2, $dataRow2['nim'])
										->setCellValue('C'.$row2, $dataRow2['nama'])
										->setCellValue('D'.$row2, $dataRow2['kode_mk'])
										->setCellValue('E'.$row2, $dataRow2['mata_kuliah'])
										->setCellValue('F'.$row2, $dataRow2['kelas'])
										->setCellValue('G'.$row2, $dataRow2['semester'])
										->setCellValue('H'.$row2, $dataRow2['id_sms'])
										->setCellValue('I'.$row2, $dataRow2['jenjang_pendidikan']);
				}
				$objPHPExcel->getActiveSheet()->removeRow($baseRow2-1,1);

				//SET SHEET dosen
				$objPHPExcel->setActiveSheetIndex(2);
				$baseRow3 = 3;
				foreach($data3 as $r3 => $dataRow3) {
					$row3 = $baseRow3 + $r3;
					$objPHPExcel->getActiveSheet()->insertNewRowBefore($row3,1);
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$row3, $r3+1)
										->setCellValue('B'.$row3, $dataRow3['nidn'])
										->setCellValue('C'.$row3, $dataRow3['dosen'])
										->setCellValue('D'.$row3, $dataRow3['kode_mk'])
										->setCellValue('E'.$row3, $dataRow3['mata_kuliah'])
										->setCellValue('F'.$row3, $dataRow3['kelas'])
										->setCellValue('G'.$row3, $dataRow3['rencana_tm'])
										->setCellValue('H'.$row3, $dataRow3['real_tm'])
										->setCellValue('I'.$row3, $dataRow3['semester'])
										->setCellValue('J'.$row3, $dataRow3['id_sms'])
										->setCellValue('K'.$row3, $dataRow3['jenjang_pendidikan']);
				}
				$objPHPExcel->getActiveSheet()->removeRow($baseRow3-1,1);

				//SET SHEET Nilai
				$objPHPExcel->setActiveSheetIndex(3);
				$baseRow4 = 3;
				foreach($data4 as $r4 => $dataRow4) {
					$row4 = $baseRow4 + $r4;
					$objPHPExcel->getActiveSheet()->insertNewRowBefore($row4,1);
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$row4, $r4+1)
										->setCellValue('B'.$row4, $dataRow4['nim'])
										->setCellValue('C'.$row4, $dataRow4['nama'])
										->setCellValue('D'.$row4, $dataRow4['kode_mk'])
										->setCellValue('E'.$row4, $dataRow4['mata_kuliah'])
										->setCellValue('F'.$row4, $dataRow4['semester'])
										->setCellValue('G'.$row4, $dataRow4['kelas'])
										->setCellValue('H'.$row4, $dataRow4['nilai_angka'])
										->setCellValue('I'.$row4, $dataRow4['nilai_huruf'])
										->setCellValue('J'.$row4, $dataRow4['nilai_indeks'])
										->setCellValue('K'.$row4, $dataRow4['id_sms'])
										->setCellValue('L'.$row4, $dataRow4['jenjang_pendidikan']);
				}
				$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);

				$filename = time().'-template-kelas.xlsx';

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				//$objWriter->save('php://output');
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
				case 0: //Kelas kuliah
					$objPHPExcel->setActiveSheetIndex(0);
					$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
					$jml_row = $objPHPExcel->getActiveSheet()->getHighestRow()-1;
					foreach ($cell_collection as $cell) 
					{
						$column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
						$row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
						$data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
						if ($row == 1) {
							$header[$row][$column] = $data_value;
						} else {
							$arr_data[$row][$column] = $data_value;
						}
					}
					if ($arr_data) 
					{
						$id_sms = '';
						$id_mk = '';
						$sks_mk = '';
						$sks_tm = '';
						$sks_prak = '';
						$sks_prak_lap = '';
						$sks_sim = '';
						$temp_data = array();
						$sukses_count = 0;
						$error_count = 0;
						$error_msg = array();
						$sukses_msg = array();
						foreach ($arr_data as $key => $value) 
						{
							$kode_mk = $value['B'];
							$nm_mk = $value['C'];
							$semester = trim($value['D']);
							$kelas = $value['E'];
							$kode_prodi = trim($value['F']);

							/**
							$filter_sms = "id_sp='".$this->session->userdata('id_sp')."' AND kode_prodi='".$kode_prodi."'";
							$temp_sms = $this->feeder->getrecord($this->session->userdata('token'),'sms',$filter_sms);
							if ($temp_sms['result']) {
								$id_sms = $temp_sms['result']['id_sms'];
							}
							**/

							$id_sms = $value['G'];
							$filter_mk = "kode_mk='".$kode_mk."' AND id_sms='".$id_sms."'";
							$temp_mk = $this->feeder->getrecord($this->session->userdata('token'),'mata_kuliah',$filter_mk);
							if ($temp_mk['result']) {
								$id_mk = $temp_mk['result']['id_mk'];
								$sks_mk = $temp_mk['result']['sks_mk'];
								$sks_tm = $temp_mk['result']['sks_tm']==''?'0':$temp_mk['result']['sks_tm'];
								$sks_prak = $temp_mk['result']['sks_prak']==''?'0':$temp_mk['result']['sks_prak'];
								$sks_prak_lap = $temp_mk['result']['sks_prak_lap']==''?'0':$temp_mk['result']['sks_prak_lap'];
								$sks_sim = $temp_mk['result']['sks_sim']==''?'0':$temp_mk['result']['sks_sim'];
							}
							$temp_data['id_sms'] = $id_sms;
							$temp_data['id_smt'] = $semester;
							$temp_data['id_mk'] = $id_mk;
							$temp_data['nm_kls'] = $kelas;
							$temp_data['sks_mk'] = $sks_mk;
							$temp_data['sks_tm'] = $sks_tm;
							$temp_data['sks_prak'] = $sks_prak;
							$temp_data['sks_prak_lap'] = $sks_prak_lap;
							$temp_data['sks_sim'] = $sks_sim;

							$temp_result = $this->feeder->insertrecord($this->session->userdata['token'], $this->table1, $temp_data);

							if ($temp_result['result']) {
								if ($temp_result['result']['error_desc']==NULL) {
									++$sukses_count;
									$sukses_msg[] = "<h4>Sukses</h4>Kelas perkuliahan <strong>".$kode_mk."</strong> - <strong>".$nm_mk."</strong> (Kelas ".$kelas.") berhasil ditambahkan";
								} else {
									++$error_count;
									$error_msg[] = "<h4>Error ".$temp_result['result']['error_code']." (".$kode_mk." - ".$nm_mk.")</h4>".$temp_result['result']['error_desc'];
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
									<font color=\"#3c763d\">".$sukses_count." data Kelas Kuliah baru berhasil ditambah</font>";
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
				case 1: //KRS
					$objPHPExcel->setActiveSheetIndex(1);
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
						$id_mk = '';
						$id_kls = '';
						$id_reg_pd = '';
						$id_sms = '';
						$temp_data = array();
						$sukses_count = 0;
						$error_count = 0;
						$error_msg = array();
						$sukses_msg = array();
						$error_nim = array();
						foreach ($arr_data as $key => $value) {
							$nim = $value['B'];
							$nm_mhs = $value['C'];
							$kode_mk = $value['D'];
							$nm_mk = $value['E'];
							$kelas = $value['F'];
							$semester = $value['G'];
							$jenjang_pendidikan = $value['H'];
							$id_sms = $value['I'];

							$filter = "id_sms='".$id_sms."' and id_jenj_didik='".$jenjang_pendidikan."' and kode_mk='".$kode_mk."'";
							$temp_mk = $this->feeder->getrecord($this->session->userdata('token'),'mata_kuliah',$filter);
							$id_mk = $temp_mk['result']['id_mk'];

							$filter_kls = "nm_kls='".$kelas."' and id_smt='".$semester."' and id_sms='".$id_sms."' and id_mk='".$id_mk."'";
							$temp_kls = $this->feeder->getrecord($this->session->userdata('token'),$this->table1,$filter_kls,'','','');
							$id_kls = $temp_kls['result']['id_kls'];

							$filter_mhspt = "nipd='".$nim."'";
							$temp_mhspt = $this->feeder->getrecord($this->session->userdata('token'),'mahasiswa_pt',$filter_mhspt,'','','');
							$id_reg_pd = $temp_mhspt['result']['id_reg_pd'];

							$temp_data['id_kls'] = $id_kls; //kelas kuliah
							$temp_data['id_reg_pd'] = $id_reg_pd; //mahasiswa_pt
							$temp_data['asal_data'] = 9;


							$temp_result = $this->feeder->insertrecord($this->session->userdata['token'], 'nilai', $temp_data); //nilai
							if ($temp_result['result']) {
								if ($temp_result['result']['error_desc']==NULL) {
									++$sukses_count;
									$sukses_msg[] = "<h4>Sukses</h4>KRS Mahasiswa <strong>".$nm_mhs."</strong> / <strong>".$nim."</strong> mata kuliah <strong>".$kode_mk."</strong> - <strong>".$nm_mk."</strong> berhasil ditambahkan";
								} else {
									++$error_count;
									$error_msg[] = "<h4>Error ".$temp_result['result']['error_code']." (".$nm_mhs." / ".$nm_mk.")</h4>".$temp_result['result']['error_desc'];
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
									<font color=\"#3c763d\">".$sukses_count." data KRS baru berhasil ditambah</font>";
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
				case 2: //Dosen
					$objPHPExcel->setActiveSheetIndex(2);
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
						$temp_nidn = '';
						$id_reg_ptk = '';
						$id_mk = '';
						$id_kls = '';
						$id_sms = '';
						$filter_ptk = '';
						$temp_data = array();
						$sukses_count = 0;
						$error_count = 0;
						$error_msg = array();
						$sukses_msg = array();
						$error_nim = array();
						foreach ($arr_data as $key => $value) {
							$nidn = $value['B'];
							$nm_dosen = $value['C'];
							$kode_mk = $value['D'];
							$nm_mk = $value['E'];
							$kelas = $value['F'];
							$ren_tm = $value['G'];
							$rel_tm = $value['H'];
							$semester = $value['I'];
							$jenjang_pendidikan = $value['J'];
							$id_sms = $value['K'];

							$filter_nidn = "nidn='".$nidn."'";
							$temp_nidn = $this->feeder->getrecord($this->session->userdata('token'),'dosen',$filter_nidn);
							if ($temp_nidn['result']) {
								$filter_ptk = "p.id_ptk='".$temp_nidn['result']['id_ptk']."' AND p.id_sp='".$this->session->userdata('id_sp')."'";
							}
							$temp_ptk = $this->feeder->getrecord($this->session->userdata('token'),'dosen_pt',$filter_ptk);
							if ($temp_ptk['result']) {
								$id_reg_ptk = $temp_ptk['result']['id_reg_ptk'];
							}

							$filter_mk = "kode_mk='".$kode_mk."' AND id_sms='".$id_sms."'";
							$temp_mk = $this->feeder->getrecord($this->session->userdata('token'),'mata_kuliah',$filter_mk);
							if ($temp_mk['result']) {
								$id_mk = $temp_mk['result']['id_mk'];
							}

							//Filter kelas kuliah
							$filter_kls = "p.id_mk='".$id_mk."' AND nm_kls='".$kelas."' AND p.id_smt='".$semester."'";
							$temp_kls = $this->feeder->getrecord($this->session->userdata('token'),$this->table1,$filter_kls);
							if ($temp_kls['result']) {
								$id_kls = $temp_kls['result']['id_kls'];
							}

							$temp_data['id_reg_ptk'] = $id_reg_ptk;
							$temp_data['id_kls'] = $id_kls;
							$temp_data['sks_subst_tot'] = 0;
							$temp_data['sks_tm_subst'] = 0;
							$temp_data['sks_prak_subst'] = 0;
							$temp_data['sks_prak_lap_subst'] = 0;
							$temp_data['sks_sim_subst'] = 0;
							$temp_data['jml_tm_renc'] = $ren_tm;
							$temp_data['jml_tm_real'] = $rel_tm;
							$temp_data['id_jns_eval'] = 1;

							$temp_result = $this->feeder->insertrecord($this->session->userdata['token'], 'ajar_dosen', $temp_data); // ajar_dosen
							if ($temp_result['result']) {
								if ($temp_result['result']['error_desc']==NULL) {
									++$sukses_count;
									$sukses_msg[] = "<h4>Sukses</h4>Dosen pengampuh <strong>".$nm_dosen."</strong> untuk mata kuliah <strong>".$kode_mk."</strong> - <strong>".$nm_mk."</strong> berhasil ditambahkan";
								} else {
									++$error_count;
									$error_msg[] = "<h4>Error ".$temp_result['result']['error_code']." (".$nm_dosen." / ".$nm_mk.")</h4>".$temp_result['result']['error_desc'];
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
									<font color=\"#3c763d\">".$sukses_count." data Mahasiswa baru berhasil ditambah</font>";
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
				case 3: //Nilai
					$objPHPExcel->setActiveSheetIndex(3);
					$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
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
						$id_mk = '';
						$id_sms = '';
						$id_reg_pd = '';
						$id_kls = '';
						$temp_data = array();
						$temp_data2 = array();
						$id_reg_pd = array();
						$sukses_count = 0;
						$sukses_msg = '';
						$error_count = 0;
						$error_msg = array();
						$error_nim = array();
						$array = array();
						foreach ($arr_data as $key => $value) {
							$nim = $value['B'];
							$kode_mk = $value['D'];
							$smt = $value['F'];
							$kls = $value['G'];
							$jenjang_pendidikan = $value['K'];
							$id_sms = $value['L'];

							$filter_mk = "kode_mk='".$kode_mk."' AND id_sms='".$id_sms."'";
							$temp_mk = $this->feeder->getrecord($this->session->userdata('token'),'mata_kuliah',$filter_mk);
							if ($temp_mk['result']) {
								$id_mk = $temp_mk['result']['id_mk'];
							}

							$filter_mhs = "nipd like '%".$nim."%'";
							$temp_mhs = $this->feeder->getrecord($this->session->userdata('token'),'mahasiswa_pt',$filter_mhs);
							if ($temp_mhs['result']) {
								$id_reg_pd = $temp_mhs['result']['id_reg_pd'];
							} 

							//Filter id_kls
							$filter_kls = "p.id_mk='".$id_mk."' AND p.id_smt='".$smt."' AND nm_kls='".$kls."'";
							$temp_kls = $this->feeder->getrecord($this->session->userdata('token'),'kelas_kuliah',$filter_kls);
							if ($temp_kls['result']) {
								$id_kls = $temp_kls['result']['id_kls'];
							}

							//inisial data
							$temp_key = array(	
												'id_kls' => $id_kls,
												'id_reg_pd' => $id_reg_pd
											 );
							$temp_data = array(
												'nilai_angka' => $value['H'],
												'nilai_huruf' => $value['I'],
												'nilai_indeks' => $value['J']
											 );
							$array[] = array('key'=>$temp_key,'data'=>$temp_data);
						}
						
						$temp_result = $this->feeder->updaterset($this->session->userdata('token'),'nilai',$array);

						$this->benchmark->mark('selesai');
						$time_eks = $this->benchmark->elapsed_time('mulai', 'selesai');
						$i=0;
						if ($temp_result['result']) {
							foreach ($temp_result['result'] as $key) {
								++$i;
								if ($key['error_desc']==NULL) {
									++$sukses_count;
								} else {
									++$error_count;
									$error_msg[] = "<h4>Error baris ".$i."</h4>".$key['error_desc'];
								}
							}
						} else {
							echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error ".$temp_result['error_code']."</h4>".$temp_result['error_desc']."</div></div>";
						}

						if ((!$sukses_count==0) || (!$error_count==0)) {
							echo "<div class=\"alert alert-warning\" role=\"alert\">
									Waktu eksekusi ".$time_eks." detik<br />
									Results (total ".$i." baris data):<br /><font color=\"#3c763d\">".$sukses_count." data nilai berhasil diupdate</font><br />
									<font color=\"#ce4844\" >".$error_count." data error (tidak bisa diupdate) </font>";
									if (!$error_count==0) {
										echo "<a data-toggle=\"collapse\" href=\"#collapseExample\" aria-expanded=\"false\" aria-controls=\"collapseExample\">Detail error</a>";
									}
									echo "<div class=\"collapse\" id=\"collapseExample\">";
											foreach ($error_msg as $pesan) {
													echo "<div class=\"bs-callout bs-callout-danger\">".$pesan."</div><br />";
												}
									echo "</div>
								</div>";
						}
					} else {
						echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>Tidak dapat mengekstrak file.. Silahkan dicoba kembali</div>";
					}
					break;
			}
		}
	}

	public function jsonKLS()
	{
		$search = $this->input->post('search');
		$sSearch = trim($search['value']);
		$orders = $this->input->post('order');
		$iStart = $this->input->post('start');
		$iLength = $this->input->post('length');

		$temp_limit = $iLength;
		$temp_offset = $iStart?$iStart : 0;
		$temp_total = $this->feeder->count_all($this->session->userdata('token'),$this->table1,$this->filter);
		$totalData = $temp_total['result'];
		$totalFiltered = $totalData;
		if (!empty($sSearch)) {
			$temp_filter = "nm_mk like '%".$sSearch."%' OR nm_smt like '%".$sSearch."%'";
			$temp_rec = $this->feeder->getrset($this->session->userdata('token'),
												$this->table1, 
												$temp_filter, 
												'id_smt DESC', 
												$temp_limit,
												$temp_offset
												);
			$__total = $this->feeder->count_all($this->session->userdata('token'),$this->table1,$temp_filter);
			$totalFiltered = $__total['result'];
		} else {
			$temp_rec = $this->feeder->getrset($this->session->userdata('token'),
												$this->table1, 
												$this->filter, 
												'id_smt DESC', 
												$temp_limit,
												$temp_offset
												);
		}
		//var_dump($temp_rec);
		$temp_error_code = $temp_rec['error_code'];
		$temp_error_desc = $temp_rec['error_desc'];

		if (($temp_error_code==0) && ($temp_error_desc=='')) {
			$temp_data = array();
			$i=0;
			foreach ($temp_rec['result'] as $key) {
				$temps = array();
				$filter_sms = "id_sms = '".$key['id_sms']."'";
				$temp_sms = $this->feeder->getrecord($this->session->userdata('token'),'sms',$filter_sms);
				$filter_jenjang = "id_jenj_didik = ".$temp_sms['result']['id_jenj_didik'];
				$temp_jenjang = $this->feeder->getrecord($this->session->userdata('token'),'jenjang_pendidikan',$filter_jenjang);

				$filter_kodemk = "id_mk = '".$key['id_mk']."' AND id_sms='".$key['id_sms']."'";
				$temp_kodemk = $this->feeder->getrecord($this->session->userdata('token'),'mata_kuliah',$filter_kodemk);

				$filter_kls = "p.id_kls = '".$key['id_kls']."'";
				$count_klsmhs = $this->feeder->count_all($this->session->userdata('token'),$this->table2,$filter_kls);

				$filter_dosen = "id_kls = '".$key['id_kls']."'";
				$count_klsdosen = $this->feeder->count_all($this->session->userdata('token'),'ajar_dosen',$filter_dosen);

				$temps[] = ++$i+$temp_offset;
				$temps[] = $temp_jenjang['result']['nm_jenj_didik'].' '.$temp_sms['result']['nm_lemb'];
				$temps[] = $key['id_smt'];
				$temps[] = $temp_kodemk['result']['kode_mk'];
				$temps[] = $temp_kodemk['result']['nm_mk'];
				$temps[] = $key['nm_kls'];
				$temps[] = $key['sks_mk'];
				$temps[] = $count_klsmhs['result'];
				$temps[] = $count_klsdosen['result'];
				$temps[] = '<a href="'.base_url().'kelas/nilai/'.$key['id_kls'].'" target="_blank"><i class="fa fa-search-plus"></i></a>';
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
