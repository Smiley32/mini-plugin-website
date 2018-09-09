Element.prototype.remove = function() {
  this.parentElement.removeChild(this);
}
NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
  for(var i = this.length - 1; i >= 0; i--) {
    if(this[i] && this[i].parentElement) {
      this[i].parentElement.removeChild(this[i]);
    }
  }
}

function initSelected(category) {
  // If we define a postId variable before, we some tags already
  if(typeof postId === 'undefined') {
    return;
  }
  get(g_baseUrl + 'tags/api?category=' + category + '&post=' + postId, displaySelectedTags.bind(null, category));
}

function update(id) {
  var e = document.getElementById(id);
  get(g_baseUrl + 'tags/api?search=' + e.value + '&category=' + id, displayTags.bind(null, id));
}

function displaySelectedTags(id, data) {
  var parsed = JSON.parse(data);
  // console.log(parsed);
  for(var i = 0; i < parsed.length; i++) {
    addTag(id, parsed[i].tag);
  }
}

function displayTags(id, data) {
  var parsed = JSON.parse(data);
  // console.log(parsed);
  var html = '';
  for(var i = 0; i < parsed.length; i++) {
    html += '<div class="control">';
    html += '<div class="tags has-addons">';
    html += '<a class="tag is-link" onclick="addTag(\'' + id + '\', \'' + parsed[i].tag + '\')">' + parsed[i].tag + '</a>';
    html += '</div>';
    html += '</div>';
  }
  document.getElementById(id + '_all').innerHTML = html;
}

function addTag(category, tag) {
  removeTag(tag);

  var html = '';

  html += '<div class="control tag_selected" id="' + tag + '">';
  html += '<div class="tags has-addons">';
  html += '<a class="tag is-success">' + tag + '</a>';
  html += '<a class="tag is-delete" onclick="removeTag(\'' + tag + '\')"></a>';
  html += '</div>';
  html += '</div>';

  var e = document.getElementById(category + '_selected');
  e.innerHTML += html;

  submit();
}

function removeTag(tag) {
  var e = document.getElementById(tag);
  if(null != e) {
    e.remove();
  }
  submit();
}

function submit() {
  var elmts = document.getElementsByClassName('tag_selected');
  var value = '';
  for(var i = 0; i < elmts.length; i++) {
    if(i > 0) {
      value += ';';
    }
    value += elmts[i].id;
  }
  var e = document.getElementById('tags_search');
  e.value = value;
}

function addTagInCategory(event, category) {
  event.preventDefault();

  var e = document.getElementById(category);
  var tag = e.value;

  if('' == tag) {
    return;
  }

  get(g_baseUrl + 'tags/api?category=' + category + '&tag=' + tag, update.bind(null, category));
}

// Tags panel if used
function toggleTags() {
  isTagPanelOpen = !isTagPanelOpen;

  var panel = document.getElementById('tagPanel');
  var content = document.getElementById('pageContent');

  if(null == panel || null == content) {
    return false;
  }

  if(isTagPanelOpen) {
    // document.getElementById('tagPanel').style.display = 'block';
    panel.classList.add('opened');
    content.classList.add('moved');
  } else {
    panel.classList.remove('opened');
    content.classList.remove('moved');
  }

  return true;
}

{
  var isTagPanelOpen = false;

  if(window.innerWidth >= 1088) {
    toggleTags();
  }

  var pageContent = document.getElementById('pageContent');
  if(null != pageContent) {
    pageContent.addEventListener('click', function(event) {
      if(isTagPanelOpen) {
        if(!event.srcElement.classList.contains('toggleTags')) {
          event.preventDefault();
          toggleTags();
        }
      }
    });
  }

  var elmts = document.getElementsByClassName('tag_input');
  for(var i = 0; i < elmts.length; i++) {
    update(elmts[i].id);
    initSelected(elmts[i].id);
  }
}
