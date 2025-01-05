importScripts( './firebase-app-compat.js' );
importScripts( './firebase-messaging-compat.js' );

var firebaseConfig = {
	apiKey: "AIzaSyB9tM0QYb1D3JF07RqpeG-14ADGhezGRws",
	authDomain: "timetrex-app.firebaseapp.com",
	databaseURL: "https://timetrex-app.firebaseio.com",
	projectId: "timetrex-app",
	storageBucket: "timetrex-app.appspot.com",
	messagingSenderId: "462133047262",
	appId: "1:462133047262:web:1705b6bfca364bcd99b74f"
};

// Initialize Firebase

firebase.initializeApp( firebaseConfig );

// Retrieve an instance of Firebase Messaging so that it can handle background messages.
const messaging = firebase.messaging();

messaging.onBackgroundMessage( function( payload ) {
	//Find an open client to send a background notification to.
	payload.messageType = 'background';
	self.clients.matchAll( { includeUncontrolled: true } ).then( function( clients ) {
		clients.forEach( function( client ) {
			client.postMessage( payload );
		} );
	} );
} );
