
//Upload Profile Picture Function

function uploadProfilePicture(input) {
    const file = input.files[0];

    if (file) {
        const formData = new FormData();
        formData.append('profile_picture', file);

        fetch('./php/upload_profile_picture.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                // Refresh the page to display the updated profile picture
                location.reload();
            } else {
                throw new Error('Failed to upload profile picture.');
            }
        })
        .catch(error => {
            console.error(error);
        });
    }
}

// Edit Bio 

function editBio() {
    var bioParagraph = document.getElementById('bio');
    var bioTextarea = document.getElementById('bioTextarea');
    var editButton = document.getElementById('editBioButton');
    var saveButton = document.getElementById('saveBioButton');

    // Remove any leading or trailing white spaces and line breaks from the bio text
    var bioText = bioParagraph.innerHTML.trim().replace(/<br>/g, '');

    // Set the textarea value to the current bio text
    bioTextarea.value = bioText;

    // Hide the bio paragraph and show the textarea and save button
    bioParagraph.style.display = 'none';
    bioTextarea.style.display = 'block';
    editButton.style.display = 'none';
    saveButton.style.display = 'block';
}

function saveBio() {
    // Get the new bio value from the textarea
    var newBio = document.getElementById('bioTextarea').value;

    // Check if the new bio exceeds the character limit
    if (newBio.length > 60) {
        alert('Bio exceeds the character limit of 60.');
        return;
    }

    // Check if the new bio exceeds the line limit
    var lines = newBio.split('\n');
    if (lines.length > 3) {
        alert('Bio exceeds the line limit of 3.');
        return;
    }

    // Send an AJAX request to update the bio value in the database
    var xhr = new XMLHttpRequest();
    xhr.open('POST', './php/update_bio.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Bio saved successfully
                var bioParagraph = document.getElementById('bio');
                bioParagraph.innerHTML = newBio.replace(/\n/g, '<br>');

                // Hide the textarea and save button, and show the bio paragraph
                bioParagraph.style.display = 'block';
                document.getElementById('bioTextarea').style.display = 'none';
                document.getElementById('saveBioButton').style.display = 'none';
                document.getElementById('editBioButton').style.display = 'flex';
            } else {
                // Error occurred while saving the bio
                alert('Failed to save the bio.');
            }
        }
    };
    xhr.send('bio=' + encodeURIComponent(newBio));
}


// Location system
function showPosition(position) {
  var latitude = position.coords.latitude;
  var longitude = position.coords.longitude;

  // Send an AJAX request to retrieve the location using OpenCage Geocoder API
  var xhr = new XMLHttpRequest();
  var url = `https://api.opencagedata.com/geocode/v1/json?q=${latitude}+${longitude}&key=a6b61a3aee6c41a6b57a31667a5cc59d`;
  xhr.open('GET', url, true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        var response = JSON.parse(xhr.responseText);
        if (response.results.length > 0) {
          var components = response.results[0].components;
          var city = components.city;
          var town = components.town;

          // Choose the city or town value based on availability
          var location = city ? city : town;
          if (location) {
            // Save the city/town to the database or perform any further actions
            saveLocationToDatabase(location, latitude, longitude);
          } else {
            console.log('City/town not found in the location data.');
          }
        } else {
          console.log('Failed to retrieve location.');
        }
      } else {
        console.log('Failed to retrieve location.');
      }
    }
  };
  xhr.send();
}

function saveLocationToDatabase(location, latitude, longitude) {
  // Send an AJAX request to update the user's location in the database
  var xhr = new XMLHttpRequest();
  xhr.open('POST', './php/update_location.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        // Location saved successfully
        alert('Location saved');
        // Reload the page to display the updated location
        window.location.reload();
      } else {
        // Error occurred while saving the location
        alert('Failed to save the location.');
      }
    }
  };
  xhr.send(
    'location=' +
      encodeURIComponent(location) +
      '&latitude=' +
      encodeURIComponent(latitude) +
      '&longitude=' +
      encodeURIComponent(longitude)
  );
}

// Get the user's location and save the location
function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  } else {
    alert('Geolocation is not supported by this browser.');
  }
}

// - - - - - - - - - - - - - --  Friend Request system - - - - - --  -- - - - - - - -


function acceptFriendRequest(requestId, senderUsername) {
  // Send an AJAX request to accept the friend request
  var xhr = new XMLHttpRequest();
  var url = 'http://reto-date.com/php/accept_friend_request.php?id=' + requestId;
  xhr.open('POST', url, true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
          // Display a success message or handle the response as needed
          console.log('Friend request accepted for ' + senderUsername);
          location.reload();
      }
  };
  xhr.send('id=' + requestId);
}

// Add the following JavaScript function to handle the decline button action
function declineFriendRequest(requestId, senderUsername) {
  // Send an AJAX request to decline the friend request
  var xhr = new XMLHttpRequest();
  var url = 'http://reto-date.com/php/decline_friend_request.php?id=' + requestId;
  xhr.open('POST', url, true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
      if (xhr.readyState === XMLHttpRequest.DONE) {
          if (xhr.status === 200) {
              // Request was successful
              var response = xhr.responseText;
              if (response === 'success') {
                  // Handle the success case (e.g., display a message or update the UI)
                  console.log('Friend request declined');
                  location.reload();
              } else {
                  // Handle any error cases
                  console.error('Failed to decline friend request');
              }
          } else {
              // Handle request errors
              console.error('Failed to send request');
          }
      }
  };
  xhr.send();
}

// Count friend request
function countFriendRequests() {
  var friendRequestList = document.getElementById("friendRequestList");
  var friendRequests = friendRequestList.getElementsByTagName("li");
  var count = friendRequests.length;
  var countText = `${count} new user`;
  if (count !== 1) {
      countText += "s";
  }

  document.getElementById("friendRequestCount").textContent = countText;
}

//- - - - -  Button Bubble Animation

var animateButton = function(e) {

  e.preventDefault;
  //reset animation
  e.target.classList.remove('animate');
  
  e.target.classList.add('animate');
  setTimeout(function(){
    e.target.classList.remove('animate');
  },700);
};

var bubblyButtons = document.getElementsByClassName("bubbly-button");

for (var i = 0; i < bubblyButtons.length; i++) {
  bubblyButtons[i].addEventListener('click', animateButton, false);
}