$(document).ready(function()
	{
		$( "#result" ).hide();
		$( "#waiting" ).hide();

		$( "#monForm" ).submit(function( event )
    	{
    		$("#result").html("");
    		$( "#waiting" ).show();
    		event.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: $(this).serialize(),
                success: function(html)
                {
                	$( "#waiting" ).fadeOut('fast');
                	$( "#result" ).fadeIn();
                    $( "#result" ).append(html);
                }
            });

        });
      
    });