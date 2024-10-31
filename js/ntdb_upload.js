jQuery(document).ready(function($)
{	var mediaUploader;

	$('#ntdb-upload-button').click(function(e)
	 { e.preventDefault();

	   //--- Reopen uploader is already created:
		 if (mediaUploader)
		    { mediaUploader.open();
			    return;
		    }

	   mediaUploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose CSV data file',
		  	library: {
        orderby: 'date',
        query: true, 
        post_mime_type: ['text/csv']
        },
				button: {
				text: 'Use as tiny DB'
		    }, 
			  multiple: false });

	   mediaUploader.on('select', function()
	   { attachment = mediaUploader.state().get('selection').first().toJSON();
	     $('#optTinyDB').val(attachment.url);
       $('#divTinyDB').text(attachment.url);
		 });

	   mediaUploader.open();
	});
});
