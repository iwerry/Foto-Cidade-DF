/**
 * FotoCidade DF - JavaScript Principal
 * Gerencia interações da interface, modais, filtros e inicialização dos ícones Lucide.
 */

document.addEventListener('DOMContentLoaded', function () {
  // Inicializa os ícones do Lucide
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  // --- Modais ---
  window.openModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }
  };

  window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = '';
    }
  };

  // Fechar modais ao clicar no backdrop (overlay)
  document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) {
        backdrop.closest('.modal-container').classList.add('hidden');
        backdrop.closest('.modal-container').classList.remove('flex');
        document.body.style.overflow = '';
      }
    });
  });

  // --- Menu Mobile ---
  const btnMobileMenu = document.getElementById('btn-mobile-menu-toggle');
  const mobileMenu = document.getElementById('mobile-drawer-menu');
  if (btnMobileMenu && mobileMenu) {
    btnMobileMenu.addEventListener('click', function () {
      mobileMenu.classList.toggle('hidden');
    });
  }

  // --- Sistema de Curtidas na Vitrine ---
  window.toggleLike = function (buttonElement) {
    const countSpan = buttonElement.querySelector('.like-count');
    const heartIcon = buttonElement.querySelector('.heart-icon');
    let count = parseInt(countSpan.textContent || '0', 10);
    const isLiked = buttonElement.getAttribute('data-liked') === 'true';

    if (isLiked) {
      count--;
      buttonElement.setAttribute('data-liked', 'false');
      buttonElement.classList.remove('text-rose-600', 'bg-rose-50');
      buttonElement.classList.add('text-slate-500', 'hover:text-rose-500');
      if (heartIcon) heartIcon.setAttribute('fill', 'none');
    } else {
      count++;
      buttonElement.setAttribute('data-liked', 'true');
      buttonElement.classList.remove('text-slate-500', 'hover:text-rose-500');
      buttonElement.classList.add('text-rose-600', 'bg-rose-50');
      if (heartIcon) heartIcon.setAttribute('fill', 'currentColor');
    }
    countSpan.textContent = count;
  };

  // --- Filtros Virtuais da Vitrine ---
  const filterBtns = document.querySelectorAll('.filter-btn');
  if (filterBtns.length > 0) {
    filterBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        filterBtns.forEach(function (b) {
          b.classList.remove('bg-[#0D5BA8]', 'text-white', 'shadow-xs');
          b.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200', 'hover:bg-slate-50');
        });
        btn.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-200', 'hover:bg-slate-50');
        btn.classList.add('bg-[#0D5BA8]', 'text-white', 'shadow-xs');

        const filterCategory = btn.getAttribute('data-filter');
        const items = document.querySelectorAll('.vitrine-card');

        items.forEach(function (item) {
          if (filterCategory === 'Todos' || item.getAttribute('data-category') === filterCategory) {
            item.style.display = 'block';
          } else {
            item.style.display = 'none';
          }
        });
      });
    });
  }

  // --- Busca na Vitrine ---
  const searchInput = document.getElementById('search-vitrine-input');
  if (searchInput) {
    searchInput.addEventListener('input', function (e) {
      const term = e.target.value.toLowerCase();
      const items = document.querySelectorAll('.vitrine-card');

      items.forEach(function (item) {
        const text = item.textContent.toLowerCase();
        if (text.includes(term)) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  }
});
