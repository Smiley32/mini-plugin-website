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
  };
  xhttp.open('GET', url, true);
  xhttp.send();
}

/**
 * Send a post request and call the callback function
 *
 * @param {string}                          url         The url to send to
 * @param {function(content: string): void} callback    The callback function
 * @param {object}                          object      A (json) object to send. Not a string
 */
function post(url, callback, object) {
  var xhttp = new XMLHttpRequest();
  xhttp.open('POST', url, true);
  xhttp.setRequestHeader('Content-Type', 'application/json');
  xhttp.onreadystatechange = function() {
    if(this.readyState == 4 && this.status == 200) {
      if(null != callback && undefined != callback) {
        callback(this.responseText);
      }
    }
  };

  var data = JSON.stringify(object);
  xhttp.send(data);
}
