function addToFoursquare(vid, message) {
  window.open('http://foursquare.com/remote_todo?vid=' + vid + '&message=' + message,
              'Add To-Do to foursquare',
              'toolbar=0,status=0,height=380,width=650,scrollbars=no,resizable=no')
}