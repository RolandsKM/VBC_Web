function scrollCategories(direction) {
    const categoryList = document.querySelector('.category-list');
    const scrollAmount = 200;

    if (direction === 1) {
        categoryList.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    } else if (direction === -1) {
        categoryList.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    }
}

