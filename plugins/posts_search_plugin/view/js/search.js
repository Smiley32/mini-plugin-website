function getParam(param) {
  var params = window.location.search.substr(1);

  if('' == params) {
    return null;
  }

  var array = params.split('&');
  for(var i = 0; i < array.length; i++) {
    var a = array[i].split('=');

    if(a[0] == param) {
      return a[1] ? a[1] : '';
    }
  }

  return null;
}

function goPage(id) {
  var params = window.location.search.substr(1);

  var url = '?';
  if('' != params) {
    var tags = getParam('tags');
    if(undefined != tags) {
      url += 'tags=' + tags + '&';
    }

    var submit = getParam('submit');
    if(undefined != submit) {
      url += 'submit=' + submit + '&';
    }

    if(!displayTagPanel) {
      url += 'displayTagPanel=0&';
    } else {
      url += 'displayTagPanel=1&';
    }
  }

  url += 'page=' + id;

  location.href = url;
}

/*/
function toggleTags() {
  displayTagPanel = !displayTagPanel;
  if(displayTagPanel) {
    document.getElementById('tagPanel').style = 'display: block;';
  } else {
    document.getElementById('tagPanel').style = 'display: none;';
  }
}
/*/

function toggleFavorite(elmt, id) {
  get('/posts/api?favorite=' + id, null); // TODO: handle callback
  elmt.classList.toggle('loved');
}

var displayTagPanel = true;
{
  var param = getParam('displayTagPanel');
  if(0 == param) {
    // toggleTags();
  }
}

var displayedDotMenu = null;
function displayDotMenu(id, event) {
  console.log(event);
  event.stopPropagation();
  if(displayedDotMenu == null && id != null) {
    displayedDotMenu = id;
    document.getElementById(id).style.display = 'block';
  } else if(displayedDotMenu != null) {
    document.getElementById(displayedDotMenu).style.display = 'none';
    displayedDotMenu = null;
  }
}

document.getElementById('main-body').addEventListener('click', displayDotMenu.bind(null, null), false);
