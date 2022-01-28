function hideTotal() {
  $("#total").hide();
  $('#spendenupload').hide();
}
function randomString(length, chars) {
    var result = '';
    for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
    return result;
}
function updateProgress() {
  var totalSize = 0; //total.size is broken
  for(var i = 0; i < uploader.files.length; ++i) {
    var file = uploader.files[i];
    totalSize += file.size;
    $('#upload' + file.id + " .progress").css("width", 100 * file.loaded / file.size + '%');
  }
  $('#total .progress').css('width', 100 * uploader.total.loaded / totalSize + '%');
  var remaining = (totalSize - uploader.total.loaded * 1.0) / uploader.total.bytesPerSec;
  var remainingStr = Math.round(remaining/60) + ":" + (Math.round(remaining%60) < 10 ? "0" + Math.round(remaining%60) : Math.round(remaining%60));
  $('#total input').val(Math.round(uploader.total.bytesPerSec / 1000) + " kB/s - " + remainingStr + " min verbleibend")
}

function processURLs() {
  var urls = $('#urladd textarea')[0].value;
  var html = '';
  urls.match(url_regexp).forEach(function(url) {
    console.log(uploader.files.length, num_urls_to_upload);
    if(uploader.files.length + num_urls_to_upload == max_num_imgs) {
      openDialog("Ooops", "Du kannst leider nur " + max_num_imgs + " Bilder auf einmal hochladen.");
      return;
    }
    var url_id = 'img' + next_url_id++;
    html += '<div class="file" id="' + url_id + '"><div class="thumbnailcontainer">' + $("<img>").addClass("thumbnail").attr('src', url)[0].outerHTML + '</div><img src="/res/imgs/world.png" /> <span class="filesource">URL</span><a class="remove" href="javascript:;" onclick="if(noDelete) return; delete urls_to_upload[$(this.parentNode).attr(\'id\')];num_urls_to_upload--;$(this.parentNode).remove();moveSteps();"><img src="/res/imgs/cross.png"> entfernen</a><div class="filenamebackground"><div class="progress"></div><input disabled="disabled" class="filename" value="' + $('<div/>').text(url).html() + '" /></div></div>';
    urls_to_upload[url_id] = url;
    num_urls_to_upload++;
  });
  document.getElementById('filelist').innerHTML += html;
  $('#urladd').hide();
  refreshShowThumbs();
  moveSteps();
}

function refreshShowThumbs() {
  $('#showthumbs a').text("Vorschaubilder " + ($.cookie('plthumb') == "true" ? "nicht" : "wieder") + " anzeigen");
  $('#showthumbs').toggle($.cookie('plthumb') == 'false' || ($.cookie('plthumb') == 'true' && longThumbnailGeneration));
}

function moveSteps() {
  $('.step:eq(2)').css("margin-top", $("#options")[0].offsetTop - $("#container")[0].offsetTop - 20 + "px")
  $('.step:eq(3)').css("margin-top", $("#submit")[0].offsetTop - $("#options")[0].offsetTop - 35 + "px")
}
function reenableRemove() {
  $('.remove, #abloadbutton').not('.cancelUpload').css('cursor', 'pointer').css('opacity', 1);
}


//
// Upload-Optionen
//

function newGalleryDialog() {
  $('#galleryselect option').removeAttr('selected');$('#galleryselect option:first').attr("selected", "selected");
  openDialog("Neue Galerie anlegen", "\
    <input id=\"newname\" type=\"text\" placeholder=\"Name\" /><br />\
    <input id=\"newdesc\" type=\"text\" placeholder=\"Beschreibung\" /><br />\
    <button style=\"display: block; margin: 10px auto 10px auto; text-align: center;\" onclick=\"\
      $.get('https://abload.de/calls/createGallery.php?xsspin=' + xsspin() + '&amp;name=' + encodeURIComponent($('#newname').val()) + '&amp;desc=' + encodeURIComponent($('#newdesc').val()), function(response) {\
        if(isNaN(response)) {\
          $('#newanswer').html(response);\
        } else {\
          $('#galleryselect option:selected').val(response);\
          $('#galleryselect option:selected').text($('#newname').val());\
          closeDialog();\
        }}); return false;\"\
      >Galerie anlegen</button>\
      <div id=\"newanswer\"></div>\
      <b>Tipp:</b> Unter &quot;Mein Account&quot; kannst du eine Standardeinstellung definieren.");
}

function useFormat() {
  $('#resizeselect option:selected').val($('#newsize').val());
  if($('#newsize').val().match(/^[0-9]{1,4}x[0-9]{1,4}$/)) {
    $('#resizeselect option:selected').html("Passend in " + $('#newsize').val());
  } else {
    $('#resizeselect option:selected').html("Lange Seite " + $('#newsize').val() + " Pixel");
  }
  closeDialog();
}

function newResizeDialog() {
  $('#resizeselect option').removeAttr('selected');$('#resizeselect option:first').attr("selected", "selected");
  openDialog("Neues Format", "\
    <p align='left'>Nehmen wir an, dein Bild hat 2000x2000 Pixel. Gibst du nun 1000x1000 ein, hat es am Ende genau diese Größe.<br />Gibst du 1024x768 ein, wird es 768*768 Pixel groß − denn du möchtest ja kein Bild, bei dem irgendeine Seite<br />über 768 Pixel groß ist und das Seitenverhältnis soll bebeihalten werden. Alternativ könntest du einfach 768<br />eingeben, um denselben Effekt zu haben. Das Vergrößern von Bildern ist nicht möglich!</p><br />\
    <input id=\"newsize\" type=\"text\" name=\"format\" value=\"\" onclick=\"this.value = ''; this.onclick = '';\" onkeyup=\"$('#dialog button')[0].disabled = !this.value.match(/^[0-9]{1,4}(?:x[0-9]{1,4})?$/) \" /><br />\
    " + (user_logged_in ? "\
      <input type=\"checkbox\" name\"save\" id=\"saveformat\" style=\"margin-top: 10px; margin-right:-127px;\" /> <label for=\"saveformat\">Format speichern?</label>\
      <button onclick=\"\
        if($('#saveformat').val() == 'on') {\
          $.get('/calls/createResize.php?xsspin=' + xsspin() + '&amp;size=' + encodeURIComponent($('#newsize').val()));\
          useFormat();\
        } else {\
          useFormat();\
        }\
        return false;\" style=\"display: block; margin: 10px auto 10px auto; text-align: center;\"\
      disabled=\"disabled\">Format nutzen</button>\
      <b>Tipp:</b> Unter &quot;Mein Account&quot; kannst du eine Standardeinstellung definieren.\
    " : "\
      <button onclick=\"useFormat(); return false;\" style=\"display: block; margin: 10px auto 10px auto; text-align: center;\" disabled=\"disabled\">Format nutzen</button><br /><b>Tipp:</b> Als angemeldeter Benutzer könntest du deine Formate auch speichern.") + "\
    ");
}

function editResizeDialog() {
  openDialog("eigene Formate", "<div id='editformats'></div>");
  javascript:$('#editformats').load('https://abload.de/calls/editResize.php?xsspin=' + xsspin());
}

function newDeleteDialog() {
  openDialog("Eigene Dauer", "\
  <input type=\"text\" name=\"time\" id=\"newdeleted\" size=\"10\" onkeyup=\"$('#dialog button')[0].disabled = !this.value.match(/^[0-9]+$/) \" />\
  <select size=\"1\" name=\"interval\" style=\"width: 120px;\" id=\"newdeletei\">\
    <option value=\"h\">Stunde/n</option>\
    <option value=\"d\">Tag/e</option>\
    <option value=\"w\">Woche/n</option>\
    <option value=\"m\">Monat/e</option>\
    <option value=\"y\">Jahr/e</option>\
  </select><br /> \
    " + (user_logged_in ? "\
      <input type=\"checkbox\" name\"save\" id=\"savetime\" style=\"margin-top: 10px; margin-left: 321px; width: 10px;\" /> <label for=\"savetime\">Dauer speichern?</label>\
      <button onclick=\"\
        if($('#savetime').val() == 'on') {\
          $.get('/calls/createDelete.php?time=' + encodeURIComponent($('#newdeleted').val() + $('#newdeletei').val()));\
          useDelete();\
        } else {\
          useDelete();\
        }\
        return false;\"\
      disabled=\"disabled\" style=\"display: block; margin: 10px auto 10px auto; text-align: center;\">Dauer nutzen</button>\
      <b>Tipp:</b> Unter &quot;Mein Account&quot; kannst du eine Standardeinstellung definieren.\
    " : "\
      <button onclick=\"useDelete(); return false;\" disabled=\"disabled\" style=\"display: block; margin: 10px auto 10px auto; text-align: center;\">Dauer nutzen</button><br /><b>Tipp:</b> Als angemeldeter Benutzer könntest du die Dauer auch speichern.") + "\
    ");
}

function useDelete() {
  $('#deleteselect option:selected').val($('#newdeleted').val());
  $('#deleteselect option:selected').html('nach ' + $('#newdeleted').val() + ' ' + $('#newdeletei option:selected').html());
  closeDialog();
}

function editDeleteDialog() {
  openDialog("Eigene Dauer", "<div id='editdeletetime'></div>");
  javascript:$('#editdeletetime').load('https://abload.de/calls/editDelete.php?xsspin=' + xsspin());
}


noDelete = false;

urls_to_upload = {};
num_urls_to_upload = 0;
next_url_id = 0;
longThumbnailGeneration = false;

$(document).ready(function() {
  /*@cc_on
      @if( @_jscript_version < 10 )
          document.location.href = '?flashUpload=true';
      @end
  @*/

  $("#browse").click(function(e) {
      $('div.moxie-shim input[type=file]').trigger('click');
      e.stopPropagation();
  });

  uploader = new plupload.Uploader({
    browse_button: 'fakebrowse',
    url: document.location.protocol + '//' + server_id + '.abload.de/calls/newUpload.php',
    chunk_size: '100kb',
    drop_element: 'content',
    filters: {
      mime_types: [{title : "Bilddateien", extensions : flash_filetypes}],
      max_file_size: flash_filesize,
      prevent_duplicates: true,
    },
    max_file_size: flash_filesize,
    max_retries: 3,
    unique_names: true,
    multipart_params: {userID: null},
    runtimes: "html5"
  });

  uploader.init();

  uploader.bind('Init', function() {
    $('#initializing').hide();
    $('#uploadfield').show();
    moveSteps();
  })

  uploader.bind('FilesAdded', function(up, files) {
    if(up.files.length + num_urls_to_upload > max_num_imgs) {
      var deleted = uploader.files.splice(max_num_imgs - num_urls_to_upload).length;
      files.splice(files.length - deleted);
      openDialog("Ooops", "Du kannst leider nur " + max_num_imgs + " Bilder auf einmal hochladen.");
    }
    var html = '';
    var start = new Date().getTime();
    plupload.each(files, function(file) {
      html += '<div class="file" id="upload' + file.id + '"><div class="thumbnailcontainer"><img class="thumbnail" /></div><img src="/res/imgs/house.png" /> <span class="filesource">LOKAL</span><a class="remove" href="javascript:;" onclick="uploader.removeFile(this.parentNode.id.substr(6));$(this.parentNode).remove();moveSteps();"><img src="/res/imgs/cross.png"> entfernen</a><div class="filenamebackground"><div class="progress"></div><input disabled="disabled" class="filename" value="' + $('<div/>').text(file.name).html() + '" /></div></div>';

      var preloader = new moxie.image.Image();
      var imgid = '#upload' + file.id + ' .thumbnail';
      preloader.onload = function() {
      	preloader.downsize( 50, 50 );
      	$(imgid).prop("src", preloader.getAsDataURL() );
        preloader.destroy();
        var end = new Date().getTime();
        if(end-start > 3000) {
          longThumbnailGeneration = true;
          if($.cookie('plthumb') == undefined && start > 0) {
            openDialog("Vorschauen anzeigen?", "Um das Erkennen deiner Dateien zu erleichtern, blenden wir neben dem Dateinamen eine Vorschau des Bildes ein.<br>Es scheint aber, dass das lokale Berechnen dieser Vorschau bei dir recht lange dauert.<br><br>Willst du trotzdem weiterhin Vorschaubilder sehen?<button onclick=\"$.cookie('plthumb', true, { expires: 365 });closeDialog();\">Ja, Vorschaubilder anzeigen, auch wenn es dadurch etwas länger dauert</button><button onclick=\"$.cookie('plthumb', false, { expires: 365 });closeDialog();\">Nein, Vorschaubilder nicht anzeigen</button>");
            start = 0;
          }
          refreshShowThumbs();
        }
      };
      if($.cookie('plthumb') == undefined || $.cookie('plthumb') == "true") preloader.load( file.getSource() );
    });
    refreshShowThumbs();
    document.getElementById('filelist').innerHTML += html;
    moveSteps();
  });

  uploader.bind('Error', function(up, error) {
    if(error.file) {
      console.log(error);
      var text = error.message;
      if(error.code == -600) text = "Das Bild ist größer als " + (flash_filesize / 1024 / 1024) + " MB.";
      if(error.code == -601) text = "Der Dateityp wird nicht unterstützt.";
      if(error.code == -602) text = "Das Bild wurde bereits hinzugefügt.";
      document.getElementById('filelist').innerHTML += '<div class="file" id="upload' + error.file.id + '"><div class="thumbnailcontainer"><img class="thumbnail" /></div><img src="/res/imgs/house.png" /> <span class="filesource">LOKAL - Fehler: ' + text + '</span><a class="remove" href="javascript:;" onclick="uploader.removeFile(this.parentNode.id.substr(6));$(this.parentNode).remove();moveSteps();"><img src="/res/imgs/cross.png"> entfernen</a><div class="filenamebackground"><div class="progress"></div><input disabled="disabled" class="filename error" value="' + $('<div/>').text(error.file.name).html() + '" /></div></div>';
    } else {
      if(error.code == -500) {
        window.location.href = 'indexf545.html?flashUpload=true';
      } else {
        openDialog("Fehler", "Es ist etwas schief gegangen: \"" + error.message + "\". Bitte kontaktiere uns <a href='/contact.php'>hier</a>.");
      }
    }
  });

  submitted = false;
  uploader.bind('UploadComplete', function(up, files) {
    if(submitted) return;
    var form = $("<form>").attr("action", document.location.protocol + "//" + server_id + ".abload.de/flashUploadFinished.php?server=" + server_id).attr("method", "POST").css("display", "none");
    form.append($("<input>").attr("name", "userID").val(userID));
    form.append($("<input>").attr("name", "resize").val($("#resizeselect").val()));
    form.append($("<input>").attr("name", "rules").val($("#rules").val()));
    if($("#galleryselect")) form.append($("<input>").attr("name", "gallery").val($("#galleryselect").val()));
    for(var id in urls_to_upload) {
      form.append($("<input>").attr("name", id).val(urls_to_upload[id]));
    }
    uploads = [];
    uploader.files.forEach(function(file) {
      uploads.push({'id': file.id, 'name': file.name});
    });
    form.append($("<input>").attr("name", 'upload_mapping').val(JSON.stringify(uploads)));
    $("body").append(form);
    if(typeof(progressInterval) !== 'undefined') window.clearInterval(progressInterval);
    updateProgress();
    $('#total input').val("Bitte warte kurz - deine Bilder werden verarbeitet").css('font-weight', 'bold');
    submitted = true;
    form.submit();
  })

  document.getElementById('abloadbutton').onclick = function() {
    if(!user_logged_in && !$('#rules:checked').length) {
      alert("Bitte akzeptiere zuerst die Nutzungsbedingungen.");
      return;
    }

    noDelete = true;
    $('.remove, #abloadbutton').not('.cancelUpload').css('cursor', 'default').css('opacity', 0.5);
    var has_urls = false;
    for(var id in urls_to_upload) {
      has_urls = true;
      break;
    }
    if(uploader.files.length == 0 && !has_urls) {
      alert("Bitte wähle erst Bilder zum Hochladen aus!");
      return;
    }
    $('#total').show();
    window.scrollTo(0,0);
    if(typeof userID === 'undefined') userID = randomString(32, '0123456789abcdefABCDEF');
    uploader.settings.multipart_params.userID = userID;
    uploader.start();
    if(uploader.files.length == 0) {
      uploader.trigger('UploadComplete', {})
    }
    progressInterval = window.setInterval(updateProgress, 1000);
    if(spendeninfo) $('#spendenupload').show();
    moveSteps();
  };

  $('#browse').mouseover(function() { $('#dragdrop').css('color', '#555') } );
  $('#browse').mouseout(function() { $('#dragdrop').css('color', '#FFF') } );

  $("#filetype a img").fadeTo(0, 0.3);
  $("#filetype a img").mouseover(function() { $(this).fadeTo(0, 1) });
  $("#filetype a img").mouseout(function() { $(this).fadeTo(0, 0.3) });
})
