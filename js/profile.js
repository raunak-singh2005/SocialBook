// Wait for the DOM to fully load before running the script
document.addEventListener('DOMContentLoaded', function() {
    // --- Location Picker Elements ---
    const addLocationBtn = document.getElementById('addLocationBtn');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const mapDiv = document.getElementById('mapPreview');
    let mapInstance = null;
    let markerInstance = null;

    // Set the location on the map and update UI
    function setLocation(lat, lng) {
        latitudeInput.value = lat;
        longitudeInput.value = lng;
        mapDiv.style.display = 'block';

        // Initialize Google Map
        mapInstance = new google.maps.Map(mapDiv, {
            center: {lat: lat, lng: lng},
            zoom: 15
        });

        // Place marker on the map
        markerInstance = new google.maps.Marker({
            position: {lat: lat, lng: lng},
            map: mapInstance
        });

        // Update button to "Remove Location"
        addLocationBtn.textContent = 'Remove Location';
        addLocationBtn.classList.add('remove-btn');
        addLocationBtn.onclick = removeLocation;
    }

    // Remove the location and reset UI
    function removeLocation() {
        latitudeInput.value = '';
        longitudeInput.value = '';
        mapDiv.style.display = 'none';
        mapDiv.innerHTML = '';
        addLocationBtn.textContent = 'Add Location';
        addLocationBtn.classList.remove('remove-btn');
        addLocationBtn.onclick = addLocation;
    }

    // Get user's current location and set it
    function addLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                setLocation(position.coords.latitude, position.coords.longitude);
            }, function() {
                alert('Unable to retrieve your location.');
            });
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    }

    // Set initial button action
    addLocationBtn.onclick = addLocation;
});

// --- Profile Image Upload/Remove Functionality ---

// File input for profile image
const fileInput = document.getElementById('fileInput');
let removeBtn = null;

// Listen for file selection
fileInput.addEventListener('change', function() {
    if (fileInput.files.length > 0) {
        // Hide file input after file is selected
        fileInput.style.display = 'none';

        // Create "Remove Image" button if it doesn't exist
        if (!removeBtn) {
            removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'themed-btn remove-btn';
            removeBtn.style.marginTop = '0';
            removeBtn.textContent = 'Remove Image';
            fileInput.parentNode.insertBefore(removeBtn, fileInput.nextSibling);

            // Remove image and reset input when button is clicked
            removeBtn.onclick = function() {
                fileInput.value = '';
                fileInput.style.display = '';
                removeBtn.remove();
                removeBtn = null;
            };
        }
    }
});