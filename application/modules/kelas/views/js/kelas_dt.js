<script type="text/javascript">
	$(document).ready(function() {
		var t = $('#dt_data').DataTable({
			"aaSorting": [],
			"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
			"iDisplayLength": 10,
			//"sPaginationType": "full_numbers",
			"processing": true,
			"language": {
				"processing": "<i class=\"fa fa-spinner fa-spin\"></i> Loading data, please wait..." //add a loading image,simply putting <img src="loader.gif" /> tag.
			},
			"serverSide": true,
			"ajax": {
				"url": top_url+'kelas/jsonKLS',
				"type": "POST"
            },
			"columns": [
							{ "searchable": false, "orderable": false, "targets": 0 },
							{ "searchable": false, "orderable": false, "targets": 0 },
							{ "searchable": false, "orderable": false, "targets": 0 },
							{ "searchable": false, "orderable": false, "targets": 0 },
							{ "searchable": false, "orderable": false, "targets": 0 },
							{ "searchable": false, "orderable": false, "targets": 0 },
							{ "searchable": false, "orderable": false, "targets": 0 },
							{ "searchable": false, "orderable": false, "targets": 0 },
							{ "searchable": false, "orderable": false, "targets": 0 },
							//{ "searchable": false, "orderable": false, "targets": 0 }
						],
		});
		$.fn.dataTable.ext.errMode = 'throw';
	});

	$(".btn-download").click(function(e){
		e.preventDefault();
		var url = top_url+'kelas/createexcel/';
		var l = Ladda.create(this);
		var prodi = $('.prodi').val();
		$(".isi").hide();
		l.start();
		$.get(url+'?p='+prodi , function(returnData) {
			$(".alert").show();
			$(".isi").show();
			if (!returnData) {
				$('.isi').html('<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>No respond from server.</div>');
			} else {
				$('.isi').html(returnData);
			};
		})
		.always(function() { l.stop(); });
		return false;
	});

	(function() {
		//var bar = $('.bar');
		var bar = $('.progress-bar');
		var percent = $('.percent');
		//var status = $('#status');
		$('.frm_upload').ajaxForm({
			beforeSend: function() {
				$('.btn-upload').attr('disabled', 'disabled');
				$(".alert").show();
				$(".isi").hide();
				var percentVal = '0%';
				bar.width(percentVal)
				percent.html(percentVal);
				$(".loading").html('<i class=\"fa fa-spinner fa-spin\"></i> Upload '+percentVal);
			},
			uploadProgress: function(event, position, total, percentComplete) {
				var percentVal = percentComplete + '%';
				//bar.width(percentVal);
				bar.attr('style', percentVal);
				if (percentVal=='100%') {
					percent.html('100%');
					percent.html('Extract File..please wait');
					$(".loading").html('<i class=\"fa fa-spinner fa-spin\"></i> Extract file, please wait...');
				} else {
					percent.html('Upload '+percentVal);
					$(".loading").html('<i class=\"fa fa-spinner fa-spin\"></i> Upload file '+percentVal);
				}
				
				//console.log(percentVal, position, total);
			},
			success: function(data) {
				var percentVal = '100%';
				//bar.width(percentVal)
				bar.attr('style', percentVal);
				percent.html(percentVal);
				$('.btn-upload').removeAttr('disabled');
				$('#dt_data').DataTable().ajax.reload();
				$(".isi").html(data);
			},
			complete: function(xhr) {
				$('.btn-upload').removeAttr('disabled');
				$(".loading").empty();
				$(".isi").show();
			},
			error: function() {
				$('.btn-upload').removeAttr('disabled');
				$('.isi').html('<div class=\"bs-callout bs-callout-danger\"><h4>Error</h4>No respond from server.</div>');
			},
		}); 
	})();


	Ladda.bind('.btn-download', { timeout: 2000 } );
	Ladda.bind('.btn-download', {
		callback: function( instance ) {
			var progress = 0;
			var interval = setInterval( function() {
				progress = Math.min( progress + Math.random() * 0.1, 1 );
				instance.setProgress( progress );
				if( progress === 1 ) {
					instance.stop();
					clearInterval( interval );
				}
			}, 200 );
		}
	});
	
</script>