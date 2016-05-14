<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WS Client Feeder Mahasiswa Module
 * 
 * @author 		Yusuf Ayuba
 * @copyright   2015
 * @link        http://jago.link
 * @package     https://github.com/virbo/wsfeeder
 * 
*/

class Mahasiswa extends CI_Controller {

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
			$this->table = 'mahasiswa';
			$this->table1 = 'mahasiswa_pt';
			$this->table2 = 'nilai_transfer';
			$this->load->model('m_feeder','feeder');
			$this->load->helper('csv');
			$this->load->library('excel');
			$this->template = './template/mhs_template.xlsx';

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
		$this->mhs();
	}

	public function mhs()
	{
		$temp_rec = $this->feeder->getrecord($this->session->userdata('token'), $this->table1, $this->filter);
		$temp_sp = $this->session->userdata('id_sp');
		if (($temp_rec['error_desc']=='') && ($temp_sp=='') ){
			$this->session->set_flashdata('error','Kode PT Anda tidak ditemukan, silahkan masukkan kode PT anda dengan benar');
			redirect('welcome/setting');
		}
		
		$data['error_code'] = $temp_rec['error_code'];
		$data['error_desc'] = $temp_rec['error_desc'];
		$data['site_title'] = 'Daftar Mahasiswa';
		$data['title_page'] = 'Daftar Mahasiswa';
		$data['ket_page'] = 'Menampilkan dan mengelola data mahasiswa';
		$data['assign_js'] = 'js/mahasiswa_dt.js';
		$data['assign_modal'] = 'layout/modal_big_tpl.php';
		tampil('mahasiswa_view',$data);
	}

	public function nilaipindah($id_reg_pd='')
	{
		if (!empty($id_reg_pd)) {
			$filter_nilai = "p.id_reg_pd='".$id_reg_pd."'";
			$temp_nilai = $this->feeder->getrset($this->session->userdata('token'), 
													$this->table2, $filter_nilai,
													$this->order, '',''
							);
			//var_dump($temp_nilai['result']);
			$temp_jml = count($temp_nilai['result']);
			$data['nilai_pindah'] = $temp_nilai['result'];
			$data['jml'] = $temp_jml;
			$this->load->view('tpl/__nilai_pindah_view',$data);
        } else {
            redirect('mahasiswa');
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
				case 0:
					//echo "Import data mahasiswa";
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
						$error_count = 0;
						$error_msg = array();
						$sukses_msg = array();
						//$i=0;
						foreach ($arr_data as $key => $value) {
							//++$i;
							$nim = $value['B'];
							$nm_mhs = $value['C'];
							$tmp_lahir =$value['D'];
							$tgl_lahir = date('Y-m-d', strtotime($value['E']));
							$jk = trim($value['F']);
							$agama = trim($value['G']);
							$ds_kel = $value['H'];
							$wilayah = trim($value['I']);
							$nm_ibu = $value['J'];
							$kode_prodi = trim($value['K']);
							$tgl_masuk = date('Y-m-d', strtotime($value['L']));
							$smt_awal = trim($value['M']);
							$stat_mhs = trim($value['N']);
							$stat_awal = trim($value['O']);
							$sks_diakui = $value['P'];
							$pt_asal = $value['Q'];
							$prodi_asal = $value['R'];

							$filter_sms = "kode_prodi='".$kode_prodi."' AND id_sp='".$this->session->userdata('id_sp')."'";
							$temp_sms = $this->feeder->getrecord($this->session->userdata('token'),'sms',$filter_sms);
							$id_sms = $temp_sms['result']?$temp_sms['result']['id_sms']:'';
							
							$temp_data['nm_pd'] = $nm_mhs;
							$temp_data['jk'] = $jk;
							$temp_data['tmpt_lahir'] = $tmp_lahir;
							$temp_data['tgl_lahir'] = $tgl_lahir;
							$temp_data['id_agama'] = $agama;
							$temp_data['id_kk'] = 0;
							$temp_data['id_sp'] = $this->session->userdata('id_sp');
							$temp_data['ds_kel'] = $ds_kel;
							$temp_data['id_wil'] = $wilayah;
							$temp_data['a_terima_kps'] = 0;
							$temp_data['stat_pd'] = $stat_mhs;
							$temp_data['id_kebutuhan_khusus_ayah'] = 0;
							$temp_data['nm_ibu_kandung'] = $nm_ibu;
							$temp_data['id_kebutuhan_khusus_ibu'] = 0;
							$temp_data['kewarganegaraan'] = 'ID';

							$temps_data['id_sms'] = $id_sms;
							$temps_data['id_sp'] = $this->session->userdata('id_sp');
							$temps_data['id_jns_daftar'] = $stat_awal;
							$temps_data['nipd'] = $nim;
							$temps_data['tgl_masuk_sp'] = $tgl_masuk;
							$temps_data['a_pernah_paud'] = 0;
							$temps_data['a_pernah_tk'] = 0;
							$temps_data['mulai_smt'] = $smt_awal;
							if ($stat_awal=='2') {
								$temps_data['sks_diakui'] = $sks_diakui;
								$temps_data['nm_pt_asal'] = $pt_asal;
								$temps_data['nm_prodi_asal'] = $prodi_asal;
							}
							$temp_result = $this->feeder->insertrecord($this->session->userdata['token'], $this->table, $temp_data);
							
							if ($temp_result['result']) {
								//Error handle
								if ($temp_result['result']['error_desc']==NULL) {
									++$sukses_count;
									$temps_data['id_pd'] = $temp_result['result']['id_pd'];
									$temps_result = $this->feeder->insertrecord($this->session->userdata['token'], $this->table1, $temps_data);
									if ($temps_result['result']) {
										if ($temps_result['result']['error_desc']=='') {
											$sukses_msg[] = "<h4>Sukses</h4>Biodata dan histori pendidikan mahasiswa <strong>".$nm_mhs."</strong> / <strong>NIM: ".$nim."</strong> berhasil ditambahkan.";
										} else {
											++$error_count;
											$error_msg[] = "<h4>Error ".$temps_result['result']['error_code']." (".$nm_mhs." / NIM: ".$nim.")</h4><strong>Histori pendidikan:</strong> ".$temps_result['result']['error_desc'];
										}
										if (($temps_result['result']['error_desc']!='') && ($temp_result['result']['error_desc']=='')) {
											$sukses_msg[] = "<h4>Sukses</h4>Biodata mahasiswa <strong>".$nm_mhs."</strong> berhasil ditambahkan.";
										}
									}
									
								} else {
									if ($temp_result['result']['error_code']==200) {
										$filter_pd = "(nm_pd='".$nm_mhs."') AND (tmpt_lahir='".$tmp_lahir."') AND (tgl_lahir='".$tgl_lahir."') AND (nm_ibu_kandung='".$nm_ibu."') AND (p.id_sp='".$this->session->userdata('id_sp')."')";
										$temp_pd = $this->feeder->getrecord($this->session->userdata('token'),$this->table,$filter_pd);
										$temps_data['id_pd'] = $temp_pd['result']['id_pd'];
										$temps_result = $this->feeder->insertrecord($this->session->userdata['token'], $this->table1, $temps_data);
										if ($temps_result['result']) {
											if ($temps_result['result']['error_desc']==NULL) {
												++$sukses_count;
												$sukses_msg[] = "<h4>Sukses</h4>Histori pendidikan mahasiswa <strong>".$nm_mhs."</strong> berhasil ditambahkan dengan <strong>NIM ".$nim."</strong>";
											} else {
												++$error_count;
												$error_msg[] = "<h4>Error ".$temp_result['result']['error_code'].' / '.$temps_result['result']['error_code']." (".$nm_mhs." / NIM: ".$nim.")</h4><strong>Biodata:</strong> ".$temp_result['result']['error_desc']."<br /><strong>Histori pendidikan:</strong> ".$temps_result['result']['error_desc']."";
											}
										}
									}
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
					}
					break;
				case 1:
					//echo "Mahasiswa Lulus/DO";
					$objPHPExcel->setActiveSheetIndex(1);
					$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
					//var_dump($cell_collection);
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
					//var_dump($header[1]);
					//var_dump($arr_data);
					if ($arr_data) {
						$id_reg_pd = '';
						$temp_data = array();
						$sukses_count = 0;
						$sukses_msg = '';
						$error_count = 0;
						$error_msg = array();
						foreach ($arr_data as $key => $value) {
							$nim = $value['B'];
							$jenis_keluar = $value['D'];
							$tgl_keluar = $value['E'];
							$jalur_skripsi = $value['F'];
							$judul_skripsi = $value['G'];
							$bulan_awal_bimbingan = $value['H'];
							$bulan_akhir_bimbingan = $value['I'];
							$sk_yudisium = $value['J'];
							$tgl_yudisium = $value['K'];
							$ipk = $value['L'];
							$no_seri_ijazah = $value['M'];
							$keterangan = $value['N'];

							//$filter_regpd = "nipd like '%".$nim."%'";
							$filter_regpd = "nipd LIKE '%".$nim."%' AND p.id_sp='".$this->session->userdata('id_sp')."'";
							$temp_regpd = $this->feeder->getrecord($this->session->userdata('token'),$this->table1,$filter_regpd);
							if ($temp_regpd['result']) {
								$id_reg_pd = $temp_regpd['result']['id_reg_pd'];
							}
							$temp_key = array('id_reg_pd' => $id_reg_pd);
							$temp_data = array('id_jns_keluar' => $jenis_keluar,
												'tgl_keluar' => $tgl_keluar,
												'ket' => $keterangan,
												'jalur_skripsi' => $jalur_skripsi,
												'judul_skripsi' => $judul_skripsi,
												'bln_awal_bimbingan' => $bulan_awal_bimbingan,
												'bln_akhir_bimbingan' => $bulan_akhir_bimbingan,
												'sk_yudisium' => $sk_yudisium,
												'tgl_sk_yudisium' => $tgl_yudisium,
												'ipk' => $ipk,
												'no_seri_ijazah' => $no_seri_ijazah
											);
							$array[] = array('key'=>$temp_key,'data'=>$temp_data);
						}
						//var_dump($temp_data);
						//var_dump($array);
						$temp_result = $this->feeder->updaterset($this->session->userdata('token'),$this->table1,$array);
						//var_dump($temp_result['result']);
						$i=0;
						if ($temp_result['result']) {
							foreach ($temp_result['result'] as $key) {
								++$i;
								if ($key['error_desc']==NULL) {
									++$sukses_count;
								} else {
									++$error_count;
									$error_msg[] = "<h4>Error baris ".$i."</h4>".$key['error_desc'];
									$stat_reg = FALSE;
								}
							}
						} else {
							echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error ".$temp_result['error_code']."</h4>".$temp_result['error_desc']."</div></div>";
						}
						$this->benchmark->mark('selesai');
						$time_eks = $this->benchmark->elapsed_time('mulai', 'selesai');

						if ((!$sukses_count==0) || (!$error_count==0)) {
							echo "Waktu eksekusi ".$time_eks." detik<br />
									Results (total ".$i." baris data):<br /><font color=\"#3c763d\">".$sukses_count." data Mahasiswa Lulus/DO berhasil diupdate</font><br />
									<font color=\"#ce4844\" >".$error_count." data tidak bisa ditambahkan </font>";
									if (!$error_count==0) {
										echo "<a data-toggle=\"collapse\" href=\"#collapseExample\" aria-expanded=\"false\" aria-controls=\"collapseExample\">Detail error</a>";
									}
									//echo "<br />Total: ".$i." baris data";
									echo "<div class=\"collapse\" id=\"collapseExample\">";
											foreach ($error_msg as $pesan) {
													echo "<div class=\"bs-callout bs-callout-danger\">".$pesan."</div><br />";
												}
									echo "</div>";
						}
					}
					break;
				case 2:
					//echo "Nilai pindahan";
					$objPHPExcel->setActiveSheetIndex(2);
					$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
					$jml_row = $objPHPExcel->getActiveSheet()->getHighestRow()-1;
					//var_dump($cell_collection);
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
					//var_dump($header[1]);
					//var_dump($arr_data);
					if ($arr_data) {
						$id_reg_pd = '';
						$id_mk = '';
						$id_sms = '';
						$temp_data = array();
						$sukses_count = 0;
						$sukses_msg = '';
						$error_count = 0;
						$error_msg = array();
						$sukses_msg = array();
						foreach ($arr_data as $key => $value) {
							$nim = $value['B'];
							$nm_mhs = $value['C'];
							$kode_mk_asal = $value['D'];
							$nm_mk_asal = $value['E'];
							$sks_asal = trim($value['F']);
							$nh_asal = trim($value['G']);
							$kode_mk_diakui = $value['H'];
							$nm_mk_diakui = $value['I'];
							$nh_akui = trim($value['J']);
							$na_akui = trim($value['K']);
							$sks_akui = trim($value['L']);
							$kode_prodi = $value['M'];

							//$filter_regpd = "nipd='".$nim."'";
							$filter_regpd = "nipd LIKE '%".$nim."%' AND p.id_sp='".$this->session->userdata('id_sp')."'";
							$temp_regpd = $this->feeder->getrecord($this->session->userdata('token'),$this->table1,$filter_regpd);
							//var_dump($temp_regpd['result']);
							if ($temp_regpd['result']) {
								$id_reg_pd = $temp_regpd['result']['id_reg_pd'];
							}

							$filter_sms = "p.id_sp='".$this->session->userdata('id_sp')."' AND kode_prodi='".$kode_prodi."'";
							$temp_sms = $this->feeder->getrecord($this->session->userdata('token'),'sms',$filter_sms);
							if ($temp_sms['result']) {
								$id_sms = $temp_sms['result']['id_sms'];
							}
							//var_dump($temp_sms);
							
							$filter_mk = "kode_mk='".$kode_mk_diakui."' AND id_sms='".$id_sms."'";
							//$filter_mk = "kode_mk='".$kode_mk_diakui."'";
							$temp_mk = $this->feeder->getrecord($this->session->userdata('token'),'mata_kuliah',$filter_mk);
							//var_dump($filter_mk);
							//var_dump($temp_mk);
							if ($temp_mk['result']) {
								$id_mk = $temp_mk['result']['id_mk'];
							}
							$temp_data['id_reg_pd'] = $id_reg_pd;
							$temp_data['id_mk'] = $id_mk;
							$temp_data['kode_mk_asal'] = $kode_mk_asal;
							$temp_data['nm_mk_asal'] = $nm_mk_asal;
							$temp_data['sks_asal'] = $sks_asal;
							$temp_data['sks_diakui'] = $sks_akui;
							$temp_data['nilai_huruf_asal'] = $nh_asal;
							$temp_data['nilai_huruf_diakui'] = $nh_akui;
							$temp_data['nilai_angka_diakui'] = $na_akui;
							$temp_result = $this->feeder->insertrecord($this->session->userdata['token'], $this->table2, $temp_data);
							//var_dump($temp_result);
							if ($temp_result['result']) {
								if ($temp_result['result']['error_desc']==NULL) {
									++$sukses_count;
									$sukses_msg[] = "<h4>Sukses</h4>Nilai pindahan mata kuliah <strong>".$nm_mk_diakui."</strong> untuk mahasiswa <strong>".$nm_mhs."</strong>/<strong>".$nim."</strong> berhasil ditambahkan";
								} else {
									++$error_count;
									$error_msg[] = "<h4>Error ".$temp_result['result']['error_code']." (".$nm_mhs." / ".$nim.")</h4>".$temp_result['result']['error_desc'];
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
									<font color=\"#3c763d\">".$sukses_count." data Nilai pindahan baru berhasil ditambah</font>";
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
			}
		}
	}

	public function createexcel()
	{
		
		$this->benchmark->mark('mulai');
		if (!file_exists($this->template)) {
			echo "<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>File template tidak tersedia.</div>";
		} else {
			//Status Awal Masuk	SKS Diakui	PT Asal	PRODI Asal
			$data0 = array(array('nim' => '1234',
							'nama' => 'Mahasiswa 1',
							'tmp_lahir' => 'Banggai',
							'tgl_lahir' => '1980-08-23',
							'jk' => 'L',
							'agama' => '1',
							'ds_kel' => 'Kel. Tano Bonunungan',
							'wilayah' => '999999',
							'nm_ibu' => 'Ibuku tercinta',
							'kode_prodi' => '14401',
							'tgl_masuk' => '2015-09-20',
							'smt_masuk' => '20151',
							'stat_mhs' => 'A',
							'stat_awal' => '1',
							'sks_diakui' => '',
							'pt_asal' => '',
							'prodi_asal' => ''),
						array('nim' => '2345',
							'nama' => 'Mahasiswa 2',
							'tmp_lahir' => 'Pemalang',
							'tgl_lahir' => '1982-11-23',
							'jk' => 'P',
							'agama' => '1',
							'ds_kel' => 'Pemalang',
							'wilayah' => '999999',
							'nm_ibu' => 'Ibuku tersayang',
							'kode_prodi' => '14401',
							'tgl_masuk' => '2014-09-20',
							'smt_masuk' => '20141',
							'stat_mhs' => 'A',
							'stat_awal' => '2',
							'sks_diakui' => '50',
							'pt_asal' => 'PT Suka Maju',
							'prodi_asal' => 'Keperawatan')
				   );
			$data1 = array(array('nim' => '1234',
							'nama' => 'Mahasiswa 1',
							'jenis_keluar' => 1,
							'tgl_keluar' => '2015-09-30',
							'jalur_skripsi' => 1,
							'judul_skripsi' => 'Judul Skripsi pertama',
							'bulan_awal_bimbingan' => '2015-01-01',
							'bulan_akhir_bimbingan' => '2015-09-01',
							'sk_yudisium' => '123/09/2015',
							'tgl_yudisium' => '2015-09-30',
							'ipk' => 3,
							'no_seri_ijazah' => '',
							'keterangan' => ''),
						array('nim' => '2345',
							'nama' => 'Mahasiswa 2',
							'jenis_keluar' => 1,
							'tgl_keluar' => '2015-09-30',
							'jalur_skripsi' => 1,
							'judul_skripsi' => 'Judul Skripsi kedua',
							'bulan_awal_bimbingan' => '2015-01-01',
							'bulan_akhir_bimbingan' => '2015-09-01',
							'sk_yudisium' => '456/09/2015',
							'tgl_yudisium' => '2015-09-30',
							'ipk' => 3.7,
							'no_seri_ijazah' => '',
							'keterangan' => ''),
					);
			$data2 = array(array('nim' => '2345',
							'nama' => 'Mahasiswa 2',
							'kode_mk_asal' => 'MKDU101',
							'nm_mk_asal' => 'Agama',
							'sks_asal' => 2,
							'nh_asal' => 'A',
							'kode_mk_diakui' => 'WAT101',
							'nm_mk_diakui' => 'Agama',
							'nh_akui' => 'A',
							'na_akui' => 4,
							'sks_akui' => 3),
						array('nim' => '2345',
							'nama' => 'Mahasiswa 2',
							'kode_mk_asal' => 'MKDU102',
							'nm_mk_asal' => 'Biologi',
							'sks_asal' => 3,
							'nh_asal' => 'C',
							'kode_mk_diakui' => 'WAT102',
							'nm_mk_diakui' => 'Biologi',
							'nh_akui' => 'A',
							'na_akui' => 4,
							'sks_akui' => 3),
					);
			$objPHPExcel = PHPExcel_IOFactory::load($this->template);

			//SET SHEET Mahasiswa
			$objPHPExcel->setActiveSheetIndex(0);
			$baseRow = 3;
			foreach($data0 as $r => $dataRow) {
				$row = $baseRow + $r;
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $r+1)
									->setCellValue('B'.$row, $dataRow['nim'])
									->setCellValue('C'.$row, $dataRow['nama'])
									->setCellValue('D'.$row, $dataRow['tmp_lahir'])
									->setCellValue('E'.$row, $dataRow['tgl_lahir'])
									->setCellValue('F'.$row, $dataRow['jk'])
									->setCellValue('G'.$row, $dataRow['agama'])
									->setCellValue('H'.$row, $dataRow['ds_kel'])
									->setCellValue('I'.$row, $dataRow['wilayah'])
									->setCellValue('J'.$row, $dataRow['nm_ibu'])
									->setCellValue('K'.$row, $dataRow['kode_prodi'])
									->setCellValue('L'.$row, $dataRow['tgl_masuk'])
									->setCellValue('M'.$row, $dataRow['smt_masuk'])
									->setCellValue('N'.$row, $dataRow['stat_mhs'])
									->setCellValue('O'.$row, $dataRow['stat_awal'])
									->setCellValue('P'.$row, $dataRow['sks_diakui'])
									->setCellValue('Q'.$row, $dataRow['pt_asal'])
									->setCellValue('R'.$row, $dataRow['prodi_asal']);
			}
			$objPHPExcel->getActiveSheet()->removeRow($baseRow-1,1);

			//SET SHEET Mahasiswa Lulus/DO
			$objPHPExcel->setActiveSheetIndex(1);
			$baseRow1 = 3;
			foreach($data1 as $r => $dataRow) {
				$row = $baseRow1 + $r;
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $r+1)
									->setCellValue('B'.$row, $dataRow['nim'])
									->setCellValue('C'.$row, $dataRow['nama'])
									->setCellValue('D'.$row, $dataRow['jenis_keluar'])
									->setCellValue('E'.$row, $dataRow['tgl_keluar'])
									->setCellValue('F'.$row, $dataRow['jalur_skripsi'])
									->setCellValue('G'.$row, $dataRow['judul_skripsi'])
									->setCellValue('H'.$row, $dataRow['bulan_awal_bimbingan'])
									->setCellValue('I'.$row, $dataRow['bulan_akhir_bimbingan'])
									->setCellValue('J'.$row, $dataRow['sk_yudisium'])
									->setCellValue('K'.$row, $dataRow['tgl_yudisium'])
									->setCellValue('L'.$row, $dataRow['ipk'])
									->setCellValue('M'.$row, $dataRow['no_seri_ijazah'])
									->setCellValue('N'.$row, $dataRow['keterangan']);
			}
			$objPHPExcel->getActiveSheet()->removeRow($baseRow1-1,1);

			//SET SHEET Nilai Pindahan
			$objPHPExcel->setActiveSheetIndex(2);
			$baseRow2 = 3;
			foreach($data2 as $r => $dataRow) {
				$row = $baseRow2 + $r;
				$objPHPExcel->getActiveSheet()->insertNewRowBefore($row,1);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $r+1)
									->setCellValue('B'.$row, $dataRow['nim'])
									->setCellValue('C'.$row, $dataRow['nama'])
									->setCellValue('D'.$row, $dataRow['kode_mk_asal'])
									->setCellValue('E'.$row, $dataRow['nm_mk_asal'])
									->setCellValue('F'.$row, $dataRow['sks_asal'])
									->setCellValue('G'.$row, $dataRow['nh_asal'])
									->setCellValue('H'.$row, $dataRow['kode_mk_diakui'])
									->setCellValue('I'.$row, $dataRow['nm_mk_diakui'])
									->setCellValue('J'.$row, $dataRow['nh_akui'])
									->setCellValue('K'.$row, $dataRow['na_akui'])
									->setCellValue('L'.$row, $dataRow['sks_akui']);
			}
			$objPHPExcel->getActiveSheet()->removeRow($baseRow2-1,1);

			$filename = time().'-template-mhs.xlsx';

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

	public function jsonMHS()
	{
		$search = $this->input->post('search');
		$sSearch = trim($search['value']);

		//$Data = $this->input->get('columns');
		$orders = $this->input->post('order');
		//$temp_order = 

		$iStart = $this->input->post('start');
		$iLength = $this->input->post('length');

		$temp_limit = $iLength;
		$temp_offset = $iStart?$iStart : 0;
		$temp_total = $this->feeder->count_all($this->session->userdata('token'),$this->table1,$this->filter);
		$totalData = $temp_total['result'];
		$totalFiltered = $totalData;

		if (!empty($sSearch)) {
			$temp_filter = "((nm_pd LIKE '%".$sSearch."%') OR (nipd LIKE '%".$sSearch."%') AND (p.id_sp='".$this->session->userdata('id_sp')."'))";
			//$temp_filter = "nm_pd like '%".$sSearch."%' OR nipd like '%".$sSearch."%'";
			$temp_rec = $this->feeder->getrset($this->session->userdata('token'),
												$this->table1, $temp_filter,
												'nipd DESC',$temp_limit,$temp_offset
						);
			//$totalFiltered = count($temp_rec['result']);
			$__total = $this->feeder->count_all($this->session->userdata('token'),$this->table1,$temp_filter);
			$totalFiltered = $__total['result'];
		} else {
			$temp_filter = "p.id_sp='".$this->session->userdata('id_sp')."'";
			$temp_rec = $this->feeder->getrset($this->session->userdata('token'),
												$this->table1, $temp_filter,
												'nipd DESC',$temp_limit,$temp_offset
						);
			//var_dump($temp_rec['result']);
		}
		$temp_error_code = $temp_rec['error_code'];
		$temp_error_desc = $temp_rec['error_desc'];

		if (($temp_error_code==0) && ($temp_error_desc=='')) {
			$temp_data = array();
			$i=0;
			foreach ($temp_rec['result'] as $key) {
				$temps = array();
				$temps[] = ++$i+$temp_offset;
				$temps[] = $key['nipd'];
				$temps[] = $key['nm_pd'];
				$temps[] = date('d-m-Y',strtotime($key['tgl_lahir']));
				//$temps[] = $key['fk__sms'];
				$filter_sms = "id_sms='".$key['id_sms']."'";
				$temp_sms = $this->feeder->getrecord($this->session->userdata('token'),'sms',$filter_sms);
				//var_dump($temp_sms['result']);
				$filter_jenjang = "id_jenj_didik='".$temp_sms['result']['id_jenj_didik']."'";
				$temp_jenjang = $this->feeder->getrecord($this->session->userdata('token'),'jenjang_pendidikan',$filter_jenjang);
				//var_dump($temp_jenjang['result']);
				$link = $key['id_jns_daftar']==2?' <a href="javascript:void();" class="modalButton" data-toggle="modal" data-src="'.base_url().'mahasiswa/nilaipindah/'.$key['id_reg_pd'].'" data-target="#modalku"><i class="fa fa-external-link"></i></a>':'';
				$temps[] = $temp_jenjang['result']['nm_jenj_didik'].' '.$key['fk__sms'];
				//$temps[] = $key['fk__jns_daftar'].$link;
				$temps[] = $key['fk__jns_daftar'];
				$temps[] = substr($key['mulai_smt'], 0,4);

				$temp_label = strtoupper(substr($key['fk__jns_keluar'], 0,1));
				$label = $temp_label==''?'label-primary':'';
				$label .= $temp_label=='L'?'label-success':'';
				$label .= $temp_label=='M'?'label-danger':'';
				$label .= $temp_label=='D'?'label-warning':'';
				$label .= $temp_label=='N'?'label-default':'';
				$label .= $temp_label=='C'?'label-info':'';
				$label .= $temp_label=='G'?'label-primary':'';
				$label .= $temp_label=='X'?'label-default':'';
				$status = $key['fk__jns_keluar']==''?'Aktif':$key['fk__jns_keluar'];
				$temps[] = '<span class="label '.$label.'">'.$status.'</span>';
				$temps[] = '<a href="#"><i class="fa fa-search-plus"></i></a>';
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
