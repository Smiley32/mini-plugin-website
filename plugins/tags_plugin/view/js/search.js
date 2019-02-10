function searchTags() {
	var input = document.getElementById('searchTagInput');

	if(input.value != '') {
		document.location.href = g_baseUrl + 'posts/search?tags=' + input.value;
	}
}

var previousValue = '';
var beforeWord = '';
var afterWord = '';
function searchTagsAutoComplete() {
	var e = document.getElementById('searchTagInput');
	var currentValue = e.value;

	if(currentValue == '') {
		previousValue = '';
		beforeWord = '';
		afterWord = '';
		hideAutoCompleteSearchList();
		return;
	}

	console.log("current: " + currentValue);
	console.log("previous: " + previousValue);

	beforeWord = '';
	var currentWord = '';
	afterWord = '';

	var end = false;
	for(var i = 0; i < currentValue.length && i < previousValue.length; i++) {
		if(currentValue[i] != previousValue[i]) {
			end = true;
			console.log("ici 0");
		}

		if(
			currentValue[i] != '!' &&
			currentValue[i] != '-' &&
			currentValue[i] != ';' &&
			currentValue[i] != '|' &&
			currentValue[i] != ' ' &&
			currentValue[i] != '(' &&
			currentValue[i] != ')'
		) {
			console.log("ici 1");
			currentWord = currentWord + currentValue[i];
		} else {
			if(end) {
				afterWord = currentValue.substring(i);
				console.log("ici 2");
				break;
			} else {
				beforeWord = beforeWord + currentWord + currentValue[i];
				console.log("ici 3");
				currentWord = '';
			}
		}
	}

	if(!end && currentValue.length > previousValue.length) {
		if(
			currentValue[currentValue.length - 1] != '!' &&
			currentValue[currentValue.length - 1] != '-' &&
			currentValue[currentValue.length - 1] != ';' &&
			currentValue[currentValue.length - 1] != '|' &&
			currentValue[currentValue.length - 1] != ' ' &&
			currentValue[currentValue.length - 1] != '(' &&
			currentValue[currentValue.length - 1] != ')'
		) {
			currentWord = currentWord + currentValue[currentValue.length - 1];
		}
	}

	console.log("before word : " + beforeWord);
	console.log("word : " + currentWord);
	console.log("after word : " + afterWord);
	previousValue = currentValue;

	// currentWord.replace('@', '%40');

  if(e.value != '') {
    get(g_baseUrl + 'tags/api?search=' + currentWord, searchTagsAutoCompleteCallback);
  }
}

var searchSelectedSuggestion = null;
function searchTagsAutoCompleteCallback(json) {
	var parsed = JSON.parse(json);

  var html = '';
  for(var i = 0; i < parsed.length && i < 15; i++) {
    html += '<li><a class="searchSuggestion" onclick="setSearchTagInInput(\'' + parsed[i].tag + '\')">' + parsed[i].tag + '</a></li>'
  }

  document.getElementById('search-autocomp-list').innerHTML = html;
  searchSelectedSuggestion = null;
  showAutoCompleteSearchList();
}

function showAutoCompleteSearchList() {
  document.querySelector('.search-autocomp-menu').classList.remove('hided');
}

function hideAutoCompleteSearchList() {
  document.querySelector('.search-autocomp-menu').classList.add('hided');
}

function setSearchTagInInput(tag) {
	var e = document.getElementById('searchTagInput');
  e.value = beforeWord + tag + afterWord;
  hideAutoCompleteSearchList();
}

function selectSearchNextSuggestion() {
  var suggestions = document.querySelectorAll('.searchSuggestion');

  if(suggestions.length == 0) {
    searchSelectedSuggestion = null;
  } else {
    if(searchSelectedSuggestion === null) {
      searchSelectedSuggestion = 0;
    } else if(searchSelectedSuggestion == suggestions.length - 1) {
      suggestions[searchSelectedSuggestion].style.backgroundColor = 'white';
      searchSelectedSuggestion = 0;
    } else {
      suggestions[searchSelectedSuggestion].style.backgroundColor = 'white';
      searchSelectedSuggestion += 1;
    }
  }

  if(searchSelectedSuggestion !== null) {
    suggestions[searchSelectedSuggestion].style.backgroundColor = '#f5f5f5';
  }
}

function selectSearchPreviousSuggestion() {
  var suggestions = document.querySelectorAll('.searchSuggestion');

  if(suggestions.length == 0) {
    searchSelectedSuggestion = null;
  } else {
    if(searchSelectedSuggestion === null) {
      searchSelectedSuggestion = suggestions.length - 1;
    } else if(searchSelectedSuggestion == 0) {
      suggestions[searchSelectedSuggestion].style.backgroundColor = 'white';
      searchSelectedSuggestion = suggestions.length - 1;
    } else {
      suggestions[searchSelectedSuggestion].style.backgroundColor = 'white';
      searchSelectedSuggestion -= 1;
    }
  }

  if(searchSelectedSuggestion !== null) {
    suggestions[searchSelectedSuggestion].style.backgroundColor = '#f5f5f5';
  }
}

function onInputKeyDownSearch(event) {
  if(event.keyCode == 40) { // down
    selectSearchNextSuggestion();
  } else if(event.keyCode == 38) {
    selectSearchPreviousSuggestion();
  }

  if(event.keyCode == 13) { // Return key pressed
    if(searchSelectedSuggestion === null) {
      document.getElementById('searchInputButton').click()
    } else {
      var suggestions = document.querySelectorAll('.searchSuggestion');
			setSearchTagInInput(suggestions[searchSelectedSuggestion].innerHTML);
      suggestions[searchSelectedSuggestion].style.backgroundColor = 'white';
      searchSelectedSuggestion = null;
      document.getElementById('search-autocomp-list').innerHTML = '';
    }
  }
}
