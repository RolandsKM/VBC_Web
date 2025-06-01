function scrollCategories(direction) {
    const categoryList = document.querySelector('.category-list');
    const category = categoryList.querySelector('.category');

    if (!category) return;

    const categoryStyle = window.getComputedStyle(category);
    const categoryWidth = category.offsetWidth;

    // Use gap instead of marginRight (since gap is now controlling spacing)
    const gap = parseFloat(getComputedStyle(categoryList).gap || 0);
    const scrollAmount = categoryWidth + gap;

    categoryList.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
