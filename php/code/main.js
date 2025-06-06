
// 1. Dark Mode Toggle
// 2. Tab Switching
// 3. Search Through Artist Cards
// 4. Favorite Artist Selection
// 5. Modal for Delete Confirmation Modal for Profile Section
// 6. Expandable Artist Cards 
// 7. Add Artist Card Functionality

// Dark Mode Toggle
function myFunction() {
    document.body.classList.toggle("dark-mode");
}



// Tab Switching
function showContent(tabId) {
    // Remove the "active" class from all tabs
    document.querySelectorAll(".tab").forEach(tab => tab.classList.remove("active"));
    // Hide all content sections
    document.querySelectorAll(".content").forEach(content => content.style.display = "none");
    // Find the tab element that matches the given tabId using the data-tab attribute
    const activeTab = document.querySelector(".tab[data-tab=" + tabId + "]");//
     
    // If the matching tab exists, add the "active" class to highlight it
    if (activeTab) activeTab.classList.add("active");
               
    // Find the content section with the given ID
    const activeContent = document.getElementById(tabId);
    
    // If the matching content section exists, show it
    if (activeContent) activeContent.style.display = "block";
}

// Page Load Initialization
document.addEventListener("DOMContentLoaded", () => {
    showContent("all");





    // 3. Search Through Artist Cards
    document.querySelector(".circle-search input").addEventListener("input", function () {
        // listens for input in the search bar when the search bar is selected
        // Get the current input element
        let input = this;

         
        let filter = input.value.toUpperCase();

        
        let cards = document.querySelectorAll(".artist-card");

        // Look through the 'artist-card' elements
        cards.forEach(card => {
            // looks in artist card for the element artist name
            let nameElement = card.querySelector(".artist-name");

            // Get text content of the artist's name, ensuring compatibility across browsers
            let txtValue = nameElement.textContent || nameElement.innerText;

            // Check if the artist's name contains the filter text (case-insensitive)
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                // If it matches, display the card
                card.style.display = "block";
            } else {
                // If it doesn't match, hide the card
                card.style.display = "none";
            }
        });
    });
});

  
// Favorite Artist Selection 
// Select all elements with the class "artist-card" and iterate over each
document.querySelectorAll(".artist-card").forEach(card => {
    // Create a new span element to hold the music note emoji
    const checkbox = document.createElement("span");
    checkbox.textContent = "🎵"; // Use music note emoji 
    checkbox.classList.add("favorite-checkbox"); // class for  identification
    checkbox.style.cursor = "pointer"; // Make it clear it's clickable
    checkbox.style.fontSize = "30px"; // emoji size

    
    card.appendChild(checkbox); // Add the music note to the artist card
    // Add an event listener to handle clicks 
    checkbox.addEventListener("click", function () {
        // Select the container inside the favorite section where favorite cards will be shown
        let favoritesContainer = document.querySelector("#favorite .card-container");
        // Get the artist's name from the card
        let artistName = card.querySelector(".artist-name").textContent;
        
        // Toggle the favorite state 
        const isSelected = this.dataset.selected === "true";//if the checkbox is selected it will be true or false 
  
        
        if (!isSelected) {
            // If not selected, make it a favorite
            this.dataset.selected = "true";
            
            // Clone the artist card
            let clonedCard = card.cloneNode(true);
            // Remove the checkbox from the cloned card to avoid duplicate controls in favorites
            clonedCard.querySelector(".favorite-checkbox").remove();
            
            // Check if the artist is already in the favorites container
            let alreadyAdded = Array.from(favoritesContainer.querySelectorAll(".artist-card")).some(favCard =>
                favCard.querySelector(".artist-name").textContent === artistName
            );
            
            // If the artist is not already in favorites, append the cloned card
            if (!alreadyAdded) favoritesContainer.appendChild(clonedCard);
        } else {
            // If selected, remove from favorites
            this.dataset.selected = "false";
            
            // Remove the artist card from favorites
            document.querySelectorAll("#favorite .artist-card").forEach(favCard => {
                if (favCard.querySelector(".artist-name").textContent === artistName) {
                    favCard.remove();
                }
            });
        }
    });
});



// account 
let modal = document.getElementById('id01');

function showDeleteConfirmation() {
    document.getElementById('profileSection').style.display = 'none';
    document.getElementById('deleteSection').style.display = 'block';
}

function showProfile() {
    document.getElementById('deleteSection').style.display = 'none';
    document.getElementById('profileSection').style.display = 'block';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    let modal = document.getElementById('id01');
    if (event.target == modal) {// If the clicked element is the modal itself
        modal.style.display = "none";
        // Reset to profile view when modal closes
        showProfile();//
    }
}

// Remove the old card click handler and replace with event delegation
document.addEventListener('click', (event) => {
  // Check if the clicked element is an artist card or inside one
  const clickedCard = event.target.closest('.artist-card');
  
  if (clickedCard) {
    // Remove expanded class from all cards
    document.querySelectorAll('.artist-card.expanded').forEach(c => c.classList.remove('expanded'));
    // Add expanded class to clicked card
    clickedCard.classList.add('expanded');
    return; // Exit early so the collapse logic below doesn't run
  }
  
  // Existing collapse logic (when clicking outside)
  const expandedCard = document.querySelector('.artist-card.expanded');
  if (
    !expandedCard ||
    event.target.matches('input[type="checkbox"]')
  ) {
    return;
  }
  expandedCard.classList.remove('expanded');
});


// Sample artist data
const artistData = [
  {
    name: "Kendrick Lamar",
    image: "images/kdot.jpg",
    description: "Kendrick Lamar is an American rapper and songwriter known for his progressive musical styles and socially conscious songwriting.",
    albums: ["Mr. Morale & The Big Steppers"],
    songs: ["HUMBLE.", "DNA."]
  },
  {
    name: "The Weeknd",
    image: "images/weeknd.webp",
    description: "The Weeknd is a Canadian singer, songwriter, and record producer known for his dark wave-influenced R&B and pop music.",
    albums: ["Dawn FM"],
    songs: ["Blinding Lights", "Save Your Tears"]
  }
];

function addArtistCard() {
  // Find the tab with id="all"
  const allTab = document.getElementById('all');
  if (!allTab) {
    console.error('Element with id="all" not found');
    return;
  }

  // Create the card container within the "all" tab 
  let cardContainer = allTab.querySelector('.card-container');
  if (!cardContainer) {
    cardContainer = document.createElement('div');
    cardContainer.className = 'card-container';//
    allTab.appendChild(cardContainer);
  }

  // Get random artist data
  const randomIndex = Math.floor(Math.random() * artistData.length);
  const artist = artistData[randomIndex];

  // Create the card HTML
  const cardHTML = `
    <div class="artist-card">
      <img src="${artist.image}" alt="${artist.name}'s Photo" class="artist-photo">
      <div class="artist-info">
        <h2 class="artist-name">${artist.name}</h2>
        <p class="artist-description">${artist.description}</p>
        <div class="albums">
          <h3>Albums 2024:</h3>
          <ul>
            ${artist.albums.map(album => `<li>${album}</li>`).join('')}
          </ul>
        </div>
        <div class="songs">
          <h3>Songs Released in 2024:</h3>
          <ul>
            ${artist.songs.map(song => `<li>${song}</li>`).join('')}
          </ul>
        </div>
      </div>
    </div>
  `;

  // Add the card to the container
  cardContainer.innerHTML += cardHTML;// 
}
