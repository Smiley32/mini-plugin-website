var elmtDescription = document.getElementById('infosContent');
var elmtDescriptionTab = document.getElementById('infos');
var elmtComments = document.getElementById('commentsContent');
var elmtCommentsTab = document.getElementById('comments');
var elmtMore = document.getElementById('moreContent');
var elmtMoreTab = document.getElementById('more');
var elmtEdit = document.getElementById('editContent');
var elmtEditTab = document.getElementById('edit');
var elmtImageClick = document.getElementById('imageClick');

var globalXClick = -1;
var globalYClick = -1;

var selectedSuggestion = null;

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

  if(null != elmtImageClick) {
    elmtImageClick.addEventListener('click', onImageClick);

    var img = document.getElementById('imageInsideClick');

    if(img.complete) {
      setImageWidth();
    } else {
      img.addEventListener('load', setImageWidth);
    }
  }
}

function setImageWidth() {
  // Display tags
  get(g_baseUrl + '/tags/api?post=' + postId, displayTagsOnImage);

  var img = document.getElementById('imageInsideClick');
  elmtImageClick.style.maxWidth = img.offsetWidth + 'px';
}

function displayTagsOnImage(json) {
  var parsed = JSON.parse(json);

  // console.log(parsed);

  var html = '';

  for(var i = 0; i < parsed.length; i++) {
    html += '<div class="one-tag">';
    html += '<span class="floating-tag" style="top:' + parsed[i].y + '%; left:' + parsed[i].x + '%;"></span>';
    html += '<div class="tags has-addons tag-label" style="top:' + parsed[i].y + '%; left:' + parsed[i].x + '%;">';
    html += '<span class="tag">' + parsed[i].tag + '</span>';
    html += '<a class="tag is-delete" onclick="removeTagFromPost(event, \'' + parsed[i].tag + '\')"></a>';
    html += '</div>';
    html += '</div>';
  }

  document.getElementById('tag-container').innerHTML = html;
}

function getPosition(element) {
  var xPosition = 0;
  var yPosition = 0;

  while(element) {
    if(element.tag == "BODY") {
      var xScrollPos = element.scrollLeft || document.documentElement.scrollLeft;
      var yScrollPos = element.scrollTop || document.documentElement.scrollTop;

      xPosition += (element.offsetLeft - xScrollPos + element.clientLeft);
      yPosition += (element.offsetTop - yScrollPos + element.clientTop);
    } else {
      xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
      yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
    }
    element = element.offsetParent;
  }

  return { x: xPosition, y: yPosition };
}

function hideAutoCompleteList() {
  document.querySelector('.tagger-autocomp-menu').classList.add('hided');
}

function showAutoCompleteList() {
  document.querySelector('.tagger-autocomp-menu').classList.remove('hided');
}

function updateTaggerAutocomp() {
  var e = document.getElementById('floating-input-tag');
  if(e.value != '') {
    get(g_baseUrl + 'tags/api?search=' + e.value + '&category=Default', updateTaggerAutocompCallback);
  }
}

function setTagInInput(tag) {
  var e = document.getElementById('floating-input-tag');
  e.value = tag;
  hideAutoCompleteList();
}

function updateTaggerAutocompCallback(json) {
  var parsed = JSON.parse(json);

  var html = '';
  for(var i = 0; i < parsed.length && i < 15; i++) {
    html += '<li><a class="suggestion" onclick="setTagInInput(\'' + parsed[i].tag + '\')">' + parsed[i].tag + '</a></li>'
  }

  document.getElementById('tagger-autocomp-list').innerHTML = html;
  selectedSuggestion = null;
  showAutoCompleteList();
}

var currentTag = null;

function confirmFloatingTagger() {
  var tagElement = document.getElementById('floating-input-tag');
  if(tagElement != null && tagElement != undefined) {
    var tag = tagElement.value;

    if(tag != '') {
      tagElement.value = "";

      // console.log("Click x: " + globalXClick + "% ; y: " + globalYClick + "%");

      // console.log(g_baseUrl + 'posts/api?post=' + postId + '&tag=' + tag + '&x=' + globalXClick + '&y=' + globalYClick);
      get(g_baseUrl + 'posts/api?post=' + postId + '&tag=' + tag + '&x=' + globalXClick + '&y=' + globalYClick, function(json) {
        get(g_baseUrl + '/tags/api?post=' + postId, displayTagsOnImage);
      });

      hideFloatingTagger();
    }
  }
}

function hideFloatingTagger() {
  var e = document.querySelector('.floating-tagger');
  if(e != null && e != undefined) {
    e.classList.add('hided');
  }
}

function showFloatingTagger(x, y) {
  // console.log("salut!");
  var width = window.innerWidth || d.documentElement.clientWidth || d.getElementsByTagName('body')[0].clientWidth;

  var useRight = false;
  if(x > width - 300) {
    useRight = true;
  }

  var e = document.querySelector('.floating-tagger');
  if(e != null && e != undefined) {
    if(useRight) {
      x = width - x;
      e.style.left = "";
      e.style.right = x + "px";
    } else {
      e.style.left = x + "px";
      e.style.right = "";
    }

    e.style.top = y + "px";
    e.classList.remove('hided');

    document.getElementById('floating-input-tag').focus();
  }
}

function onImageClick(event) {

  var elementPosition = event.target.getBoundingClientRect();

  var xClick = (event.clientX - elementPosition.left) / event.currentTarget.offsetWidth * 100;
  var yClick = (event.clientY - elementPosition.top) / event.currentTarget.offsetHeight * 100;

  globalXClick = xClick;
  globalYClick = yClick;

  showFloatingTagger(event.clientX, event.clientY + window.scrollY);

  /*/
  if(currentTag != null) {
    var elementPosition = event.currentTarget.getBoundingClientRect();
    var xClick = (event.clientX - elementPosition.left) / event.currentTarget.offsetWidth * 100;
    var yClick = (event.clientY - elementPosition.top) / event.currentTarget.offsetHeight * 100;

    console.log("Click x: " + xClick + "% ; y: " + yClick + "%");

    console.log(g_baseUrl + 'posts/api?post=' + postId + '&tag=' + currentTag + '&x=' + xClick + '&y=' + yClick);
    get(g_baseUrl + 'posts/api?post=' + postId + '&tag=' + currentTag + '&x=' + xClick + '&y=' + yClick, null);
    currentTag = null;
  } else {
    var elementPosition = event.currentTarget.getBoundingClientRect();
    var xClick = (event.clientX - elementPosition.left) / event.currentTarget.offsetWidth * 100;
    var yClick = (event.clientY - elementPosition.top) / event.currentTarget.offsetHeight * 100;

    console.log("Click x: " + xClick + "% ; y: " + yClick + "%");
  }
  /*/
}

function removeTagFromPost(event, tag) {
  // console.log(event);
  event.stopPropagation();
  get(g_baseUrl + '/tags/api?post=' + postId + '&remove=' + tag, function(data) {
    // console.log(data);
    get(g_baseUrl + '/tags/api?post=' + postId, displayTagsOnImage);
  });
  return true;
}

function selectNextSuggestion() {
  var suggestions = document.querySelectorAll('.suggestion');

  if(suggestions.length == 0) {
    selectedSuggestion = null;
  } else {
    if(selectedSuggestion === null) {
      selectedSuggestion = 0;
    } else if(selectedSuggestion == suggestions.length - 1) {
      suggestions[selectedSuggestion].style.backgroundColor = 'white';
      selectedSuggestion = 0;
    } else {
      suggestions[selectedSuggestion].style.backgroundColor = 'white';
      selectedSuggestion += 1;
    }
  }

  if(selectedSuggestion !== null) {
    suggestions[selectedSuggestion].style.backgroundColor = '#f5f5f5';
  }
}

function selectPreviousSuggestion() {
  var suggestions = document.querySelectorAll('.suggestion');

  if(suggestions.length == 0) {
    selectedSuggestion = null;
  } else {
    if(selectedSuggestion === null) {
      selectedSuggestion = suggestions.length - 1;
    } else if(selectedSuggestion == 0) {
      suggestions[selectedSuggestion].style.backgroundColor = 'white';
      selectedSuggestion = suggestions.length - 1;
    } else {
      suggestions[selectedSuggestion].style.backgroundColor = 'white';
      selectedSuggestion -= 1;
    }
  }

  if(selectedSuggestion !== null) {
    suggestions[selectedSuggestion].style.backgroundColor = '#f5f5f5';
  }
}

function onInputKeyDown(event) {
  if(event.keyCode == 40) { // down
    selectNextSuggestion();
  } else if(event.keyCode == 38) {
    selectPreviousSuggestion();
  }

  if(event.keyCode == 13) { // Return key pressed
    if(selectedSuggestion === null) {
      document.getElementById('floating-input-button').click()
    } else {
      var suggestions = document.querySelectorAll('.suggestion');
      document.getElementById('floating-input-tag').value = suggestions[selectedSuggestion].innerHTML;
      suggestions[selectedSuggestion].style.backgroundColor = 'white';
      selectedSuggestion = null;
      document.getElementById('tagger-autocomp-list').innerHTML = '';
    }
  }
}

function createLink() {
  console.log('create link...');
  var id = document.getElementById('add-link-input').value;
  get(g_baseUrl + '/posts/api?link=1&src=' + postId + '&dest=' + id, function(json) {
    location.reload();
  });
}
