// function showDetails(title, description) {
//     document.getElementById("detail-title").innerText = title;
//     document.getElementById("detail-description").innerText = description;
// }

function filterCategoriesByCity() {
  var city = document.getElementById('city_search').value;

  
  if (city.length > 0) {
     
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'get_categories_count.php?city=' + encodeURIComponent(city), true);
      xhr.onreadystatechange = function () {
          if (xhr.readyState == 4 && xhr.status == 200) {
              
              document.getElementById('categories-container').innerHTML = xhr.responseText;
          }
      };
      xhr.send();
  }
}



function scrollCategories(direction) {
    const categoryList = document.querySelector('.category-list');
    const scrollAmount = 200; 

    if (direction === 1) {
        categoryList.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    } else if (direction === -1) {
        categoryList.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    }
}

const { createApp } = Vue;

createApp({
  data() {
    return {
      posters: [
        { title: "Nokrāsot žogu", description: "Vajag palīdzību žoga nokrāsošanā. Darba apjoms: 20m²." },
        { title: "Pārvākt mēbeles", description: "Meklēju palīdzību mēbeļu pārvākšanā uz jauno dzīvokli." },
        { title: "Salabot datoru", description: "Dators neieslēdzas. Vajag palīdzību diagnostikā un remontā." },
      ],
      detailTitle: "Izvēlieties paziņojumu",
      detailDescription: "Spiediet uz paziņojuma, lai skatītu detalizētu informāciju.",
    };
  },
  methods: {
    showDetails(title, description) {
      this.detailTitle = title;
      this.detailDescription = description;
    },
  },
}).mount("#app");

