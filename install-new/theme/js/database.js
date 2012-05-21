$(document).ready(function()
{	
	// Check database configuration
	$('#btTestDB').click(function()
	{
		$("#dbResultCheck").slideUp('slow');
		$.ajax({
			url: 'index.php',
			data: 'checkDb=true&dbServer='+$('#dbServer').val()
					+'&dbName='+$('#dbName').val()
					+'&dbLogin='+$('#dbLogin').val()
					+'&dbPassword='+$('#dbPassword').val()
					+'&dbEngine='+$('#dbEngine').val()
					+'&db_prefix='+$('#db_prefix').val(),
			dataType: 'json',
			cache: false,
			success: function(json)
			{
				$("#dbResultCheck")
					.addClass((json.success) ? 'okBlock' : 'errorBlock')
					.removeClass((json.success) ? 'errorBlock' : 'okBlock')
					.html(json.message)
					.slideDown('slow');
			}
		});
	});
	
	// Check mails configuration
	if (!$('#set_stmp').attr('checked'))
		$("div#mailSMTPParam").hide();

	$("#set_stmp").click(function()
	{
		if ($("input#set_stmp").attr('checked'))
			$("div#mailSMTPParam").slideDown('slow');
		else
			$("div#mailSMTPParam").slideUp('slow');
	});

	// Send test email
	$('#btVerifyMail').click(function()
	{
		$("#mailResultCheck").slideUp('slow');
		$.ajax({
			url: 'index.php',
			data: 'sendMail=true&smtpSrv='+$('#smtpSrv').val()
					+'&smtpEnc='+$('#smtpEnc').val()
					+'&smtpPort='+$('#smtpPort').val()
					+'&smtpLogin='+$('#smtpLogin').val()
					+'&smtpPassword='+$('#smtpPassword').val()
					+'&testEmail='+$('#testEmail').val()
					+'&smtpChecked='+($('#set_stmp').attr('checked') ? 'true' : 'false'),
			dataType: 'json',
			cache: false,
			success: function(json)
			{
				$("#mailResultCheck")
					.addClass((json.success) ? 'infosBlock' : 'errorBlock')
					.removeClass((json.success) ? 'errorBlock' : 'infosBlock')
					.html(json.message)
					.slideDown('slow');
			}
		});
	});
});