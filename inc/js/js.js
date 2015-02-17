$(document).ready(function(){
	var intervalId;
	$('.check').livequery('click', function(event) { 
		event.preventDefault();
		alert("Check config.php or cats.txt or ddls.txt, something is missing");  
	});
	$('.selddls').livequery('click', function(event) { 
		event.preventDefault();
		$('#loading').show();
		$.ajax({
			type: "GET",
			url: "type.php",
			cache: false,
			data: ({'act':'selddls'}),
			success: function(data){
				$('#loading').hide();
				$('#choose').hide();
				$('#content').html(data);
				$('#content').show();
			}
		});
		return false;  
	});
	$('.sonec').livequery('click', function(event) { 
		event.preventDefault();
		var ddl = $('#sddl').val();
		var limit = $("#sddl option[value='"+ddl+"']").attr('dlimit');
		if(ddl.indexOf("g") != -1) {
			$('#ddlid').val(ddl);
			$('#ddllimit').val(limit);
			$('#submittoddl').val('Submit to all');
		}
		else if(ddl!=0) {
			var url = $("#sddl option[value='"+ddl+"']").attr('durl');
			var name = $("#sddl option[value='"+ddl+"']").attr('dname');
			$('#ddlid').val(ddl);
			$('#ddllimit').val(limit);
			$('#submittoddl').val('Submit to '+ name);
		}
		else {
			$('#ddlid').val('0');
			$('#ddllimit').val(limit);
			$('#submittoddl').val('Submit to all');
		}
		$('#loading').show();
		$.ajax({
			type: "GET",
			url: "type.php",
			cache: false,
			data: ({'act':'sonec','ddl':ddl}),
			success: function(data){
				$('#loading').hide();
				$('#choose').hide();
				$('#content').html(data);
				$('#content').show();
			}
		});
		return false;  
	});
	$('.showposts').livequery('click', function(event) { 
		event.preventDefault();
		$('#loading').show();
		var cid = $('#category').val();
		var ddl = $('#ddl').val();
		$.ajax({
			type: "GET",
			url: "type.php",
			cache: false,
			data: ({'act':'getposts','cid': cid,'ddl': ddl}),
			success: function(data){
				$('#loading').hide();
				$('#content').html(data);
			}
		});
		return false;  
	});
	
	$('.changepage').livequery('click', function(event) {
		event.preventDefault();
		var p = $(this).attr('value');
		var cid = $(this).attr('cid');
		var ddl = $(this).attr('ddl');
		$.ajax({
			type: 'GET',
			url: 'type.php',
			cache: false,
			data: ({'act':'getposts','cid': cid,'ddl': ddl,'page': p}),
			success: function(data){
				$('#content').html(data);
				$('#subtable tbody > tr').each(function(i){
					var tid = $(this).attr('tid');
					$('#tdownloads tbody tr[tid="'+tid+'"]').children('td.adddl').toggleClass('clicked');
				});
			}
		});
		
		return false;  
	});
	
	$('.sallc').livequery('click', function(event) { 
		event.preventDefault();
		$('#loading').show();
		$.ajax({
			type: "GET",
			url: "type.php",
			cache: false,
			data: ({'act':'selectddls'}),
			success: function(data){
				$('#loading').hide();
				$('#choose').hide();
				$('#content').html(data);
				$('#content').show();
			}
		});
		return false;  
	});
	$('.showddlposts').livequery('click', function(event) { 
		event.preventDefault();
		$('#loading').show();
		var ddl = $('#sddl').val();
		$.ajax({
			type: "GET",
			url: "type.php",
			cache: false,
			data: ({'act':'sallc','ddl': ddl}),
			success: function(data){
				$('#loading').hide();
				$('#content').html(data);
			}
		});
		return false;  
	});
	 $("#alltypes").livequery('change', function(event) { 
        $(".selects").val($(this).val());
      });

	$('.adddl').livequery('click', function(event) { 
		$('#submit').show();
		var dls = $('#subtable > tbody > tr').length;
		var limit = $('#ddllimit').val();
		if(dls == limit) { alert('You have '+limit+' downloads in the submit form'); $.scrollTo( '#subform' ); }
      	else {
			var tid = $(this).parent().attr('tid');
			var ttitle = $(this).parent().attr('ttitle');
			var turl = $(this).parent().attr('turl');
			var ttype = $(this).parent().attr('ttype');
			var is = $('#drow'+tid+'').length;
			if(is == 0) {
				$('#subtable > tbody:last').append('<tr id="drow'+ tid +'" tid="'+ tid +'"><td class="delete" style="cursor:pointer;width:2%;"><img src="inc/images/cross.gif" /></td><td class="ids" style="width:2%"></td><td style="width:42%;"><input type="hidden" name="pids[]" value="'+ tid +'" /><input style="width:90%;" type="text" value="'+ ttitle +'" name="title[]" /></td><td style="width:42%;"><input style="width:90%;" type="text" value="'+ turl +'" name="url[]" /></td><td style="width:12%;"><select style="width:100%;" class="selects" name="type[]" ><option value="'+ ttype +'">'+ ttype +'</option><option value="App">App</option><option value="Movie">Movie</option><option value="Game">Game</option><option value="TV">TV</option><option value="Music">Music</option><option value="Ebook">Ebook</option><option value="Mobile">Mobile</option><option value="Template">Template</option><option value="Script">Script</option><option value="Other">Other</option><option value="XXX">xxx</option></select></td></tr>');
				$('.ids').each(function(i){
					$(this).html(i + 1); // remove the +1 if you want it to start at 0
				});
				$(this).toggleClass('clicked');
			}
			else alert('You already added this download');
		}
	});
	$('#addall').livequery('click', function(event) { 
		$('#submit').show();
		var dls = $('#subtable > tbody > tr').length;
		var limit = $('#ddllimit').val();
		if(dls == limit) { alert('You have '+limit+' downloads in the submit form'); $.scrollTo( '#subform' ); }
      	else {
			$('#tdownloads > tbody > tr').each(function(){
				var dls = $('#subtable > tbody > tr').length;
				if(dls == limit) { alert('You have '+limit+' downloads in the submit form'); $.scrollTo( '#subform' ); return false;}
				else {
					var tid = $(this).attr('tid');
					var ttitle = $(this).attr('ttitle');
					var turl = $(this).attr('turl');
					var ttype = $(this).attr('ttype');
					var is = $('#drow'+tid+'').length;
					if(is == 0) {
						$('#subtable > tbody:last').append('<tr id="drow'+ tid +'" tid="'+ tid +'"><td class="delete" style="cursor:pointer;width:2%;"><img src="inc/images/cross.gif" /></td><td class="ids" style="width:2%"></td><td style="width:42%;"><input type="hidden" name="pids[]" value="'+ tid +'" /><input style="width:90%;" type="text" value="'+ ttitle +'" name="title[]" /></td><td style="width:42%;"><input style="width:90%;" type="text" value="'+ turl +'" name="url[]" /></td><td style="width:12%;"><select style="width:100%;" class="selects" name="type[]" ><option value="'+ ttype +'">'+ ttype +'</option><option value="App">App</option><option value="Movie">Movie</option><option value="Game">Game</option><option value="TV">TV</option><option value="Music">Music</option><option value="Ebook">Ebook</option><option value="Mobile">Mobile</option><option value="Template">Template</option><option value="Script">Script</option><option value="Other">Other</option><option value="XXX">xxx</option></select></td></tr>');
						$('.ids').each(function(i){
							$(this).html(i + 1); // remove the +1 if you want it to start at 0
						});
						$(this).children('td.adddl').toggleClass('clicked');
					}
					else alert('You already added this download');
				}
			});
			
		}
	});
	
	$('#subtable tr td.delete').livequery('click', function(event) { 
			var tid = $(this).parent().attr('tid');
			$("#row"+tid+' td.adddl').toggleClass('clicked');
			$(this).parent().remove();
			$('.ids').each(function(i){
				$(this).html(i + 1); // remove the +1 if you want it to start at 0
			});
			return false;
	});
	$('#backtocategories').livequery('click', function(event) { 
		var ddl = $('#ddlid').val();
		$.ajax({
			type: "GET",
			url: "type.php",
			cache: false,
			data: ({'act':'sonec','ddl':ddl}),
			success: function(data){
				$('#choose').hide();
				$('#content').html(data);
				$('#content').show();
			}
		});
		return false;
	});
	


});