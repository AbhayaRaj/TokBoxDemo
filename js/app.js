var apiKey,
    sessionId,
    token;

$(document).ready(function() {
  // Make an Ajax request to get the OpenTok API key, session ID, and token from the server
  $.get(SAMPLE_SERVER_BASE_URL + '/session.php?sender_id=1&reciver_id=2', function(result) {
    var res = JSON.parse(result);
    apiKey = res.apiKey;
    sessionId = res.sessionId;
    token = res.token;
    initializeSession();
  });
});

function initializeSession() {
  var session = OT.initSession(apiKey, sessionId);

  // Subscribe to a newly created stream
  session.on('streamCreated', function(event) {
    session.subscribe(event.stream, 'subscriber', {
      insertMode: 'append',
      width: '100%',
      height: '100%'
    });
  });

  session.on('sessionDisconnected', function(event) {
    console.log('You were disconnected from the session.', event.reason);
  });

  // Connect to the session
  session.connect(token, function(error) {
    // If the connection is successful, initialize a publisher and publish to the session
    if (!error) {
      var publisher = OT.initPublisher('publisher', {
        insertMode: 'append',
        width: '100%',
        height: '100%'
      });

      session.publish(publisher);
    } else {
      console.log('There was an error connecting to the session: ', error.code, error.message);
    }
  });
  session.on("signal", function(event) {
    alert(event.data);
    console.log(event.data);
  // Process the event.data property, if there is any data.
  });
}

/*function onbuttonClick() {
    $.get(SAMPLE_SERVER_BASE_URL + '/session.php?sender_id=1&reciver_id=2', function(result) {
    var res = JSON.parse(result);
    apiKey = res.apiKey;
    sessionId = res.sessionId;
    token = res.token;
    sendData();
  });
}
function sendData() {
  var session = OT.initSession(apiKey, sessionId);
  $.get(SAMPLE_SERVER_BASE_URL + '/session.php?action=notificationAll&session_id='+sessionId, function(result) {
    var data = JSON.parse(result)
    console.log(data);
    if(data.status) {
      session.signal(
      {
        data:data
      },
      function(error) {
        if (error) {
          console.log("signal error (" + error.code + "): " + error.message);
        } else {
          console.log("signal sent.");
        }
      }
    );
    }
  });
}*/
