// Initialize the FirebaseUI Widget using Firebase.
const ui = new firebaseui.auth.AuthUI(firebase.auth());

const uiConfig = {
  callbacks: {
    signInSuccessWithAuthResult: function(authResult, redirectUrl) {
      // User successfully signed in.
      // Get the user's ID token.
      authResult.user.getIdToken().then(function(idToken) {
        // Send the token to your backend.
        const formData = new FormData();
        formData.append('token', idToken);

        fetch('verificar_login.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            // Redirect to the appropriate page.
            window.location.assign(data.redirect);
          } else {
            // Handle errors, e.g., show a message to the user.
            console.error('Login verification failed:', data.message);
            alert('Error al iniciar sesión: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Ocurrió un error de comunicación con el servidor.');
        });
      });
      // Return false to prevent the default redirect.
      return false;
    },
    uiShown: function() {
      // The widget is rendered.
      // Hide the loader.
      document.getElementById('loader').style.display = 'none';
    }
  },
  // Will use popup for IDP Providers sign-in flow instead of the default, redirect.
  signInFlow: 'popup',
  signInSuccessUrl: 'index.php', // Where to redirect after sign-in.
  signInOptions: [
    // Leave the lines as is for the providers you want to offer your users.
    firebase.auth.GoogleAuthProvider.PROVIDER_ID,
    firebase.auth.EmailAuthProvider.PROVIDER_ID
  ],
  // Terms of service url.
  tosUrl: '<your-tos-url>',
  // Privacy policy url.
  privacyPolicyUrl: '<your-privacy-policy-url>'
};

// The start method will wait until the DOM is loaded.
ui.start('#firebaseui-auth-container', uiConfig);

// Add a loader to the page
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.createElement('div');
    loader.id = 'loader';
    loader.textContent = 'Cargando...';
    document.body.appendChild(loader);
});
