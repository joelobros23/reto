// Retrieve the user ID from the data attribute on a DOM element
const user_id = document.getElementById('user-id').dataset.userId;

// Retrieve the age input fields
const ageFromInput = document.getElementById('age-from');
const ageToInput = document.getElementById('age-to');
const genderRadioMale = document.getElementById('gender-radio-male');
const genderRadioFemale = document.getElementById('gender-radio-female');
const genderRadioAll = document.getElementById('gender-radio-all');
const searchApplyButton = document.getElementById('search-apply-button');

// Load saved options from localStorage
loadSavedOptions();

// Add event listener to the search apply button
searchApplyButton.addEventListener('click', saveOptionsAndFetchProfiles);

// Function to load saved options from localStorage
function loadSavedOptions() {
  const savedOptions = JSON.parse(localStorage.getItem('searchOptions'));
  if (savedOptions) {
    ageFromInput.value = savedOptions.ageFrom;
    ageToInput.value = savedOptions.ageTo;
    if (savedOptions.gender === 'Male') {
      genderRadioMale.checked = true;
    } else if (savedOptions.gender === 'Female') {
      genderRadioFemale.checked = true;
    } else {
      genderRadioAll.checked = true;
    }
  }
}

// Function to save options to localStorage
function saveOptions() {
  const ageFrom = ageFromInput.value || 1;
  const ageTo = ageToInput.value || 99;
  let gender;
  if (genderRadioMale.checked) {
    gender = 'Male';
  } else if (genderRadioFemale.checked) {
    gender = 'Female';
  } else {
    gender = 'All';
  }

  const options = {
    ageFrom,
    ageTo,
    gender
  };

  localStorage.setItem('searchOptions', JSON.stringify(options));
}


// Declare a variable to store the shuffled profiles
let shuffledProfiles = [];

// Function to fetch and display user profiles
function fetchProfiles() {
    const ageFrom = ageFromInput.value || 1;
    const ageTo = ageToInput.value || 99;
    const ageRange = `${ageFrom}-${ageTo}`;
      // Retrieve the selected gender from the radio buttons
     let gender;
     if (genderRadioMale.checked) {
       gender = 'Male';
     } else if (genderRadioFemale.checked) {
       gender = 'Female';
     } else {
       gender = 'All';
     }

    // Make an AJAX request to the users.php API endpoint
    return fetch(`./api/users.php?user_id=${user_id}&age=${ageRange}&gender=${gender}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.profiles.length === 0) {
                    // Handle when there are no profiles
                    clearPage();

                } else {
                    // Get the contents container
                    const contentsContainer = document.querySelector('.contents-container');
                    
                    // Filter out your own profile
                    shuffledProfiles = data.profiles.filter(profile => profile.id !== user_id);
                    // Randomize the order of profiles
                    shuffledProfiles = shuffleArray(shuffledProfiles);
                    // Initialize the current profile index
                    let currentProfileIndex = 0;
                    // Call the displayProfile function to show the first profile
                    displayProfile(shuffledProfiles[currentProfileIndex]);
                }

// Add event listener to the search apply button
searchApplyButton.addEventListener('click', fetchProfiles);

// Function to handle swiping left
function swipeLeft() {
  // Send a friend request to the current profile with the "like" action
  sendFriendRequest(user_id, shuffledProfiles[currentProfileIndex].id, 'dislike');

  // Move to the next profile
  currentProfileIndex++;
  if (currentProfileIndex < shuffledProfiles.length) {
    displayProfile(shuffledProfiles[currentProfileIndex]);
  } else {
    // Handle when there are no more profiles
    clearPage();
    console.log('No more profiles');
  }
}

// Function to handle swiping right
function swipeRight() {
  // Send a friend request to the current profile with the "like" action
  sendFriendRequest(user_id, shuffledProfiles[currentProfileIndex].id, 'like');

  // Move to the next profile
  currentProfileIndex++;
  if (currentProfileIndex < shuffledProfiles.length) {
    displayProfile(shuffledProfiles[currentProfileIndex]);
  } else {
    // Handle when there are no more profiles
    clearPage();
    console.log('No more profiles');
  }
}

function clearPage() {
    // Remove event listeners from swipe buttons
    const swipeLeftButton = document.getElementById('xbutton');
    const swipeRightButton = document.getElementById('heartbutton');
    swipeLeftButton.removeEventListener('click', swipeLeft);
    swipeRightButton.removeEventListener('click', swipeRight);

    // Clear profile elements
    var usernameElement = document.getElementById('username');
    var bioElement = document.getElementById('bio');
    var ageElement = document.getElementById('age');
    var locationElement = document.getElementById('location');
    var profilePictureElement = document.getElementById('profile-picture');
    usernameElement.textContent = '';
    bioElement.textContent = '';
    ageElement.textContent = '';
    locationElement.textContent = '';
    profilePictureElement.src = '';
    document.querySelector('.card').style.display = 'none';
        // Get the contents container
        const contentsContainer = document.querySelector('.contents-container');
    
        // Clear the contents container
        contentsContainer.innerHTML = '';

        // Create a new element for the message
        const messageElement = document.createElement('p');
        messageElement.textContent = 'No more profiles';

        // Append the message element to the contents container
        contentsContainer.appendChild(messageElement);
}


                function displayProfile(profile) {
                    // Get the profile elements
                    var usernameElement = document.getElementById('username');
                    var bioElement = document.getElementById('bio');
                    var ageElement = document.getElementById('age');
                    var locationElement = document.getElementById('location');
                    var profilePictureElement = document.getElementById('profile-picture');

                    // Update the profile data
                    usernameElement.textContent = profile.username;
                    bioElement.textContent = profile.bio;
                    ageElement.textContent = profile.age;
                    locationElement.textContent = profile.location;

                    // Fetch the profile picture from the server
                    fetchProfilePicture(profile.id)
                        .then(imagePath => {
                            // Remove the two dots from the beginning of the image path
                            imagePath = imagePath.replace('..', '.');

                            // Update the profile picture
                            profilePictureElement.src = imagePath;
                        })
                        .catch(error => {
                            console.log('Error fetching profile picture:', error);
                            // Set a default profile picture if fetching fails
                            profilePictureElement.src = 'images/profile-photo.png';
                        });

                    // Update the swipe buttons
                    const swipeLeftButton = document.getElementById('xbutton');
                    const swipeRightButton = document.getElementById('heartbutton');
                    swipeLeftButton.addEventListener('click', swipeLeft);
                    swipeRightButton.addEventListener('click', swipeRight);
                }

                // Function to fetch the profile picture path for a user
                function fetchProfilePicture(user_id) {
                    return new Promise((resolve, reject) => {
                        // Make an AJAX request to fetch the profile picture path
                        fetch(`./api/profile_pictures.php?user_id=${user_id}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Resolve the promise with the image path
                                    resolve(data.image_path);
                                } else {
                                    // Reject the promise with an error message
                                    reject('Failed to fetch profile picture');
                                }
                            })
                            .catch(error => {
                                // Reject the promise with the error
                                reject(error);
                            });
                    });
                }

                function sendFriendRequest(senderId, receiverId, action) {
  // Make an AJAX request to send the friend request
  fetch('./api/friend_request.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      sender_id: senderId,
      receiver_id: receiverId,
      action: action // Include the action parameter in the payload
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Friend request sent successfully
      console.log('Friend request sent');
      // TODO: Notify the user that the friend request was sent
    } else {
      // Failed to send the friend request
      console.log('Failed to send friend request:', data.message);
      // TODO: Display an error message to the user
    }
  })
  .catch(error => {
    console.log('Error sending friend request:', error);
    // TODO: Display an error message to the user
  });
}

        // Filter out your own profile
        shuffledProfiles = data.profiles.filter(profile => profile.id !== user_id);
        // Randomize the order of profiles
        shuffledProfiles = shuffleArray(shuffledProfiles);
        // Initialize the current profile index
        let currentProfileIndex = 0;
        // Call the displayProfile function to show the first profile
        displayProfile(shuffledProfiles[currentProfileIndex]);
    } else {
        // Handle the error if no profiles are found
        console.log(data.message);
    }
})
    .catch(error => console.log(error));
}

// Function to save options and fetch profiles
function saveOptionsAndFetchProfiles() {
  saveOptions();
  fetchProfiles();
}

// Call the loadSavedOptions function when the page loads
window.addEventListener('DOMContentLoaded', loadSavedOptions);
// Call the fetchProfiles function when the page loads
window.addEventListener('DOMContentLoaded', fetchProfiles);

// Function to shuffle an array using the Fisher-Yates algorithm
function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
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

var locationDisplay = document.getElementById('location-display').textContent;
var card = document.querySelector('.card');

function hideProfiles() {
  if (locationDisplay === '') {
    card.style.display = 'none';
  } else {
    document.querySelector('.location-system').style.display = 'none';
    document.getElementById('searching-container').style.display = 'flex';
    setTimeout(function() {
      card.style.display = 'flex';
      document.getElementById('loc-icon').style.display = 'flex';
      document.getElementById('searching-container').style.display = 'none';
    }, 5000); 
  }
}

hideProfiles();

//// Location system
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
