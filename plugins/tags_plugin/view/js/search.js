function searchTags() {
	var input = document.getElementById('searchTagInput');

	if(input.value != '') {
		document.location.href = g_baseUrl + 'posts/search?tags=' + input.value;
	}
}
