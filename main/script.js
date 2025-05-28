function scrollCategories(direction) {
    const categoryList = document.querySelector('.category-list');
    const category = categoryList.querySelector('.category');

    if (!category) return;

    const categoryStyle = window.getComputedStyle(category);
    const categoryWidth = category.offsetWidth;
    const marginRight = parseFloat(categoryStyle.marginRight || 0);
    const scrollAmount = categoryWidth + marginRight;

    categoryList.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
