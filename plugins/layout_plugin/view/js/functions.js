/**
 * Get a page an call a function on success
 *
 * @param {string}                            url           The page to get
 * @param {function(content: string): void}   callback      A function to call (with the content received)
 */
function get(url, callback) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
      // console.log(url + ' => ' + this.responseText);
      if(null != callback && undefined != callback) {
        callback(this.responseText);
      }
    }
  }
  xhttp.open('GET', url, true);
  xhttp.send();
}
