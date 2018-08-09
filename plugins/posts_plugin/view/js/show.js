var elmtDescription = document.getElementById('infosContent');
var elmtDescriptionTab = document.getElementById('infos');
var elmtComments = document.getElementById('commentsContent');
var elmtCommentsTab = document.getElementById('comments');
var elmtMore = document.getElementById('moreContent');
var elmtMoreTab = document.getElementById('more');
var elmtEdit = document.getElementById('editContent');
var elmtEditTab = document.getElementById('edit');

{
  elmtDescriptionTab.addEventListener('click', function(event) {
    elmtDescriptionTab.classList.add('is-active');
    elmtCommentsTab.classList.remove('is-active');
    elmtMoreTab.classList.remove('is-active');
    if(null != elmtEditTab) {
      elmtEditTab.classList.remove('is-active');
    }

    elmtDescription.style.display = 'block';
    elmtComments.style.display = 'none';
    elmtMore.style.display = 'none';
    if(null != elmtEdit) {
      elmtEdit.style.display = 'none';
    }
  });
  elmtDescriptionTab.click();

  elmtCommentsTab.addEventListener('click', function(event) {
    elmtDescriptionTab.classList.remove('is-active');
    elmtCommentsTab.classList.add('is-active');
    elmtMoreTab.classList.remove('is-active');
    if(null != elmtEditTab) {
      elmtEditTab.classList.remove('is-active');
    }

    elmtDescription.style.display = 'none';
    elmtComments.style.display = 'block';
    elmtMore.style.display = 'none';
    if(null != elmtEdit) {
      elmtEdit.style.display = 'none';
    }
  });

  elmtMoreTab.addEventListener('click', function(event) {
    elmtDescriptionTab.classList.remove('is-active');
    elmtCommentsTab.classList.remove('is-active');
    elmtMoreTab.classList.add('is-active');
    if(null != elmtEditTab) {
      elmtEditTab.classList.remove('is-active');
    }

    elmtDescription.style.display = 'none';
    elmtComments.style.display = 'none';
    elmtMore.style.display = 'block';
    if(null != elmtEdit) {
      elmtEdit.style.display = 'none';
    }
  });

  if(null != elmtEditTab) {
    elmtEditTab.addEventListener('click', function(event) {
      elmtDescriptionTab.classList.remove('is-active');
      elmtCommentsTab.classList.remove('is-active');
      elmtMoreTab.classList.remove('is-active');
      elmtEditTab.classList.add('is-active');

      elmtDescription.style.display = 'none';
      elmtComments.style.display = 'none';
      elmtMore.style.display = 'none';
      if(null != elmtEdit) {
        elmtEdit.style.display = 'block';
      }
    });
  }
}
