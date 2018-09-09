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
  get(g_baseUrl + 'posts/api?favorite=' + id, null); // TODO: handle callback
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
  if(displayModal) {
    return;
  }

  event.stopPropagation();
  if(displayedDotMenu == null && id != null) {
    displayedDotMenu = id;
    document.getElementById('dot-menu-' + id).style.display = 'block';
  } else if(displayedDotMenu != null) {
    document.getElementById('dot-menu-' + displayedDotMenu).style.display = 'none';
    displayedDotMenu = null;
  }
}

var displayModal = false;
function displayPools(event) {
  displayModal = true;
  event.stopPropagation();
  document.getElementById('poolModal').classList.add('is-active');
  get(g_baseUrl + 'pools/api?get=all', setPools);
}

function setPools(data) {
  console.log(data);
  var parsed = JSON.parse(data);
  if(!parsed) {
    hidePools();
  } else {
    var html = '';

    var length = parsed.length;
    for(var i = 0; i < length; i++) {
      html += '<a class="dropdown-item" onclick="putInPool(' + parsed[i]['id'] + ')">';
      html += parsed[i]['title'];
      html += '</a>';
    }

    if(html != '') {
      document.getElementById('poolModalContent').innerHTML = html;
    }
  }
}

function putInPool(pool) {
  get(g_baseUrl + 'pools/api?add=1&post=' + displayedDotMenu + '&pool=' + pool);
  hidePools();
}

function hidePools() {
  displayModal = false;
  document.getElementById('poolModal').classList.remove('is-active');
}

document.getElementById('main-body').addEventListener('click', displayDotMenu.bind(null, null), false);
