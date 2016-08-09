<div class="container-fluid">
	<div class="page-header" style="margin-top: 50px;">
		<div class="row">
			<div class="col-lg-12">
				<h3><?php echo $title_page; ?></h3>
				<small>Menampilkan data referensi wilayah. </small>	
				Klik 
				<a href="javascript:void();" class="btn btn-info btn-sm btn-download ladda-button" data-style="expand-right">
					<i class="fa fa-download"></i> di sini
				</a>
				untuk Generate dan Download data
			</div>
		</div>	
	</div>
	<div class="alert alert-warning" role="alert" style="display:none;" >
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
									<th>ID Wilayah</th>
									<th>Nama Wilayah</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th width=\"10px;\">#</th>
									<th>ID Wilayah</th>
									<th>Nama Wilayah</th>
								</tr>
							</tfoot>
						</table>";
				}
			?>
		</div>
	</div>
</div>