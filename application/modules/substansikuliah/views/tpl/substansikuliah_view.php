<div class="container-fluid">
	<div class="page-header" style="margin-top: 50px;">
		<div class="row">
			<div class="col-lg-12">
				<h3><?php echo $title_page; ?></h3>
				<small><?php echo $ket_page;?></small>

			</div>
		</div>	
	</div>
	<!--a href="javascript:void();" class="modalButton" data-toggle="modal" data-src="ws_mahasiswa/view_nilai_pindah" data-target="#modalku">
		Test
	</a-->
	<div class="row">
		<?php
			if (($error_code == 0) && ($error_desc == '')) {
				echo "<div class=\"col-ld-12 header_aksi\">
						<form action=\"".base_url()."substansikuliah/uploadexcel\" class=\"frm_upload\" id=\"frmku\" enctype=\"multipart/form-data\" method=\"post\" disabled>
							<div class=\"form-group col-xs-4\">
								<div class=\"input-group\">
									<span class=\"input-group-btn\">
										<span class=\"btn btn-success btn-file btn-sm\">
											Browse File... <input type=\"file\" name=\"userfile\" class=\"input-sm\">
										</span>
									</span>
									<input type=\"text\" class=\"form-control input-sm\" readonly>
									<span class=\"input-group-btn\">
										<button data-toggle=\"dropdown\" class=\"btn btn-sm btn-primary dropdown-toggle\"> Substansi Kuliah <span class=\"caret\"></span></button>
										<ul class=\"dropdown-menu\">
											<li><input type=\"radio\" name=\"mode\" id=\"substansikuliah\" value=\"1\" checked><label for=\"substansikuliah\">Substansi Kuliah</label></li>
									    </ul>
										<button class=\"btn btn-primary btn-sm btn-upload ladda-button\" data-style=\"expand-right\" disabled>Upload</button>
									</span>
								</div>
			                </div>
		                </form>
		                <form action=\"".base_url()."\" class=\"frm_upload\" id=\"frmku\" method=\"post\">
							<div class=\"form-group col-xs-4\">
								<div class=\"form-group\">

								    <div class=\"col-sm-4\">
								    	<select class=\"form-control input-sm prodi\" name=\"prodi\" required disabled=\"disabled\">
											<option value=\"\">-Pilih Nama Prodi-</option>
										  ";
										  		
										  	foreach ($program_studi as $key) {
										  		echo "<option value='".$key['id_sms']."|".$key['id_jenj_didik']."'>".$key['nm_lemb']."</option>";
										  	}

										  echo "
										</select>
								    </div>
								    <div class=\"col-sm-2\">
									    <a href=\"javascript:void();\" class=\"btn btn-info btn-sm btn-download ladda-button\" data-style=\"expand-right\" disabled>
											<i class=\"fa fa-download\"></i> Generate Template
										</a>
									</div>
								</div>
							</div>
						</form>
					</div>";
			}
		?>	
	</div>
	<div class="alert alert-warning" role="alert" style="display:none;" ><!-- style="display:none;"-->
		<div class="loading"></div>
		<div class="isi"></div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<?php
				if (($error_code != 0) && ($error_desc != '')) {
					echo "<div class=\"bs-callout bs-callout-danger\">
							<h4>Error ".$error_code."</h4>
							<p>";
								echo $error_desc;
								echo $error_code==100?' Silahkan generate token kembali melalui menu profil diatas.':'';
							echo "</p>
						  </div>";
				} else {
					echo "<table class=\"table table-hover table-striped table-bordered\" id=\"dt_data\">
							<thead>
								<tr>
									<th width=\"10px;\">#</th>
									<th>Nama Substansi</th>
									<th>Jenis Substansi</th>
									<th>Prog. Studi</th>
									<th>SKS MK</th>
									<th>SKS tatap muka</th>
									<th>SKS praktikum</th>
									<th>Action</th>
									
								</tr>
							</thead>
							<tbody>
								<tr>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<th>#</th>
									<th>Nama Substansi</th>
									<th>Jenis Substansi</th>
									<th>Prog. Studi</th>
									<th>SKS MK</th>
									<th>SKS tatap muka</th>
									<th>SKS praktikum</th>
									<th>Action</th>
								</tr>
							</tfoot>
						</table>";
				}
			?>
		</div>
	</div>
</div>