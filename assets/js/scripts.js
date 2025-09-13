document.addEventListener('DOMContentLoaded', function () {
    

    // Add to Cart button
    // document.querySelectorAll(".cart_btn").forEach(btn => {
    //   btn.addEventListener("click", () => {
    //     Swal.fire({
    //       icon: 'success',
    //       title: 'Товар добавлен в корзину!',
    //       // text: 'Your item has been added.',
    //       showConfirmButton: false,
    //       timer: 1000
    //     });
    //   });
    // });

    // delete Cart button
    document.querySelectorAll(".delete_icon").forEach(btn => {
      btn.addEventListener("click", () => {
        Swal.fire({
          icon: 'error',
          title: 'Товар был убран  из корзинки ',
          // text: 'Your item has been added.',
          showConfirmButton: false,
          timer: 1000
        });
      });
    });

    // Favourite button
    // document.querySelectorAll(".like_icon").forEach(btn => {
    //   btn.addEventListener("click", () => {
    //     Swal.fire({
    //       icon: 'info',
    //       title: 'Товар добавлен в избранное!',
    //       // text: 'Check your wishlist.',
    //       showConfirmButton: false,
    //       timer: 1000
    //     });
    //   });
    // });

    document.querySelectorAll(".like_icon").forEach(btn => {
    btn.addEventListener("click", () => {

    if (btn.classList.contains("active")) {
          // Уже в избранном → удаляем
          // btn.classList.remove("icon");
          Swal.fire({
            icon: 'error',
            title: 'Товар был убран  из избранного',
            showConfirmButton: false,
            timer: 1000
          });
        } else {
          // Добавляем в избранное
          // btn.classList.add("active");
          Swal.fire({
            icon: 'success',
            title: 'Товар добавлен в избранное!',
            showConfirmButton: false,
            timer: 1000
          });
        }
      });
    });

  // === awesome plate ===

  const cityInput = document.getElementById("city");
  const streetInput = document.getElementById("street");

  // список городов России
  const cities = [
    "Москва", "Санкт-Петербург", "Новосибирск", "Екатеринбург", "Нижний Новгород",
    "Казань", "Челябинск", "Самара", "Омск", "Ростов-на-Дону",
    "Уфа", "Красноярск", "Воронеж", "Пермь", "Волгоград",
    "Краснодар", "Саратов", "Тюмень", "Тольятти", "Ижевск"
  ];

  // список популярных улиц
  const streets = [
    "Ленинская", "Советская", "Центральная", "Октябрьская", "Московская",
    "Невский проспект", "Тверская", "Победы", "Гагарина", "Пушкина",
    "Кирова", "Железнодорожная", "Садовая", "Школьная", "Парковая",
    "Зеленая", "Лесная", "Набережная", "Рабочая", "Полевая",
    "Мира", "Комсомольская", "Космонавтов", "Индустриальная", "Заречная"
  ];

  // получаем элементы (проверяем наличие)
  const deliverySelect = document.getElementById('delivery_method');
  const courierBlock  = document.getElementById('courier_fields');
  const pickupBlock   = document.getElementById('pickup_info');

  // инициализация автокомплита только если элементы есть
  if (cityInput) {
    new Awesomplete(cityInput, {
      list: cities,
      minChars: 1,
      maxItems: 10
    });
  }

  if (streetInput) {
    new Awesomplete(streetInput, {
      list: streets,
      minChars: 1,
      maxItems: 10
    });
  }

  // === ЛОГИКА ПЕРЕКЛЮЧЕНИЯ СПОСОБА ДОСТАВКИ ===
  if (deliverySelect && courierBlock && pickupBlock) {
    deliverySelect.addEventListener('change', () => {
      const val = deliverySelect.value;

      if (cityInput) cityInput.removeAttribute("required");
      if (streetInput) streetInput.removeAttribute("required");

      courierBlock.style.display = 'none';
      pickupBlock.style.display = 'none';

      if (val === 'courier') {
        courierBlock.style.display = 'flex';
        if (cityInput) cityInput.setAttribute("required", "true");
        if (streetInput) streetInput.setAttribute("required", "true");
      } else if (val === 'pickup') {
        pickupBlock.style.display = 'block';
      }
    });
  }




    // Navbar burger button show/off
    window.toggleNav = function () {
      const menu = document.getElementById('navbar');
      const burger = document.getElementById('line_row');
      const header = document.getElementById('header');

      // Toggle "active" on both burger and menu
      burger.classList.toggle('active');
      menu.classList.toggle('active');
      header.classList.toggle('active');
 
    };

    // Header menu navbar dropdown for multiple items
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
      const menu = toggle.nextElementSibling; // Assumes menu comes right after toggle

      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        // // Close other dropdowns before opening this one
        // document.querySelectorAll('.dropdown-menu.active').forEach(openMenu => {
        //   if (openMenu !== menu) {
        //     openMenu.classList.remove('active');
        //     openMenu.previousElementSibling.classList.remove('active');
        //   }
        // });

        // Toggle current dropdown
        menu.classList.toggle('active');
        toggle.classList.toggle('active');
      });
    });
   

    // Close navbar when a nav link is clicked
    document.querySelectorAll('.dropdown-menu a').forEach(link => {
      link.addEventListener('click', () => {

        // if (link === dropdownToggle) return;

      const menu = document.getElementById('navbar');
      const burger = document.getElementById('line_row');
      const header = document.getElementById('header');

      // Toggle "active" on both burger and menu
      burger.classList.toggle('active');
      menu.classList.toggle('active');
      header.classList.toggle('active');
      });
    });

    // input mask
    const input = document.getElementById('phone');
    if (input) {
      Inputmask({ mask: "+7 999 999 99 99" }).mask(input);
    }

    // input mask
    const input1 = document.getElementById('phone1');
    if (input1) {
      Inputmask({ mask: "+7 999 999 99 99" }).mask(input1);
    }


    // product row swiper
    const swiperEl = document.querySelector('.envy');
    if (swiperEl) {
      new Swiper('.envy', {
        slidesPerView: 4,
        slidesPerGroup: 4, // move by 4 slides
        spaceBetween: 20,
        grabCurssor: true,
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        pagination: {
          el: '.pagination_block', // your pagination container
          clickable: true,          // allow clicking on bullets
        },

         breakpoints: {
          // when window width is >= 1200px
          1220: {
            slidesPerView: 4,
            slidesPerGroup: 4, // move by 4 slides
          },
          // when window width is >= 992px
          992: {
            slidesPerView: 3,
            slidesPerGroup: 3, // move by 4 slides
          },
          // when window width is >= 768px
          735: {
            slidesPerView:2,
            slidesPerGroup: 2, // move by 4 slides
          },
          585: {
            slidesPerView:2,
            slidesPerGroup: 2, // move by 4 slides
          },
        // when window width is < 768px
          0: {
            slidesPerView: 1,
            slidesPerGroup: 1, // move by 4 slides
          }
        },

      });
    }

    // production swiper
    const swiper = new Swiper('.preproduction', {
      slidesPerView: 2.1, // Show 2 full slides and part of the 3rd
      spaceBetween: 20, // Adjust spacing as needed
      // grabCursor: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },

        breakpoints: {
          // when window width is >= 1200px
          1220: {
            slidesPerView:2.1
          },
          // when window width is >= 992px
          992: {
            slidesPerView: 1.5
          },
          // when window width is >= 768px
          735: {
            slidesPerView: 1.2
          },
          // when window width is < 768px
          0: {
            slidesPerView: 1
          }
        },
    });

    // product gallery 
    const thumbsSwiper = new Swiper('.thumbs-swiper', {
      direction: 'vertical',
      slidesPerView: 1,
      
      spaceBetween: 0,
      freeMode: true,
      watchSlidesProgress: true,
      watchSlidesVisibility: true,
    });

    const mainSwiper = new Swiper('.main-swiper', {
      spaceBetween: 20,
      slidesPerGroup: 1, // move by 4 slides
      slidesPerView: 1,
      loop: false, // keep false if you don’t want looping
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      thumbs: {
        swiper: thumbsSwiper,
      },
    });


    // cookie modal
    // Check if cookie notice was already closed
    const modal = document.getElementById("cookieModal");
    const acceptBtn = document.getElementById("acceptCookies");

    if(modal){
      // Show modal if not accepted
      if (!localStorage.getItem("cookieAccepted")) {
        modal.style.display = "block";
      }

      // Function to close modal and remember acceptance
      function acceptCookies() {
        modal.style.display = "none";
        localStorage.setItem("cookieAccepted", "true");
      }

      // Close modal on click
      acceptBtn.addEventListener("click", acceptCookies);
    }

    // like button toogle
    const likeButtons = document.querySelectorAll('.like_icon');
      likeButtons.forEach(button => {
        button.addEventListener('click', () => {
            button.classList.toggle('active');
      });
    });

    // Accordion dropdown toggle
    document.querySelectorAll(".filter-section .section-title").forEach(title => {
      title.addEventListener("click", () => {
        title.parentElement.classList.toggle("open");
      });
    });

    // Category active toggle
  document.querySelectorAll(".category-list li").forEach(li => {
    li.addEventListener("click", () => {
      document.querySelectorAll(".category-list li").forEach(item => item.classList.remove("active"));
      li.classList.add("active");
    });
  });


  const minGapDefault = 1; // minimum difference between min and max

  // document.querySelectorAll('.filter-section').forEach(section => {
  //   const inputMin = section.querySelector('.input-min');
  //   const inputMax = section.querySelector('.input-max');
  //   const rangeMin = section.querySelector('.range-min');
  //   const rangeMax = section.querySelector('.range-max');
  //   const track = section.querySelector('.slider-track');
  //
  //   if (!inputMin || !inputMax || !rangeMin || !rangeMax || !track) {
  //     return;
  //   }
  //
  //   const minBound = Number(rangeMin.min);
  //   const maxBound = Number(rangeMin.max);
  //   const step = Number(rangeMin.step) || 1;
  //   const minGap = step * minGapDefault;
  //
  //   // keep number inputs bounds in sync with ranges
  //   inputMin.min = rangeMin.min; inputMin.max = rangeMin.max;
  //   inputMax.min = rangeMax.min; inputMax.max = rangeMax.max;
  //
  //   function updateTrack() {
  //     const a = Number(rangeMin.value);
  //     const b = Number(rangeMax.value);
  //     const total = maxBound - minBound;
  //     const left = ((a - minBound) / total) * 100;
  //     const right = ((b - minBound) / total) * 100;
  //     track.style.background = `linear-gradient(90deg, #e6e6e6 ${left}%, #00a0c6 ${left}%, #00a0c6 ${right}%, #e6e6e6 ${right}%)`;
  //   }
  //
  //   function setFromInputs() {
  //     let a = Number(inputMin.value) || minBound;
  //     let b = Number(inputMax.value) || maxBound;
  //     if (a < minBound) a = minBound;
  //     if (b > maxBound) b = maxBound;
  //     if (a > b - minGap) a = b - minGap;
  //     if (b < a + minGap) b = a + minGap;
  //     rangeMin.value = a;
  //     rangeMax.value = b;
  //     inputMin.value = a;
  //     inputMax.value = b;
  //     updateTrack();
  //   }
  //
  //   function setFromRanges(e) {
  //     let a = Number(rangeMin.value);
  //     let b = Number(rangeMax.value);
  //
  //     // Prevent crossing
  //     if (b - a < minGap) {
  //       if (e.target === rangeMin) {
  //         a = b - minGap;
  //         rangeMin.value = a;
  //       } else {
  //         b = a + minGap;
  //         rangeMax.value = b;
  //       }
  //     }
  //
  //     inputMin.value = a;
  //     inputMax.value = b;
  //     updateTrack();
  //   }
  //
  //   // events
  //   inputMin.addEventListener('input', setFromInputs);
  //   inputMax.addEventListener('input', setFromInputs);
  //   rangeMin.addEventListener('input', setFromRanges);
  //   rangeMax.addEventListener('input', setFromRanges);
  //
  //   // initialize track and keep values consistent
  //   setFromInputs();
  // });


    const resetBtn = document.getElementById('btn-reset');
    if(resetBtn){
      resetBtn.addEventListener('click', () => {
        document.querySelectorAll('.filter-section').forEach(section => {
          const rangeMin = section.querySelector('.range-min');
          const rangeMax = section.querySelector('.range-max');
          const inputMin = section.querySelector('.input-min');
          const inputMax = section.querySelector('.input-max');

          if (!rangeMin || !rangeMax || !inputMin || !inputMax) return;

          // Reset values
          rangeMin.value = rangeMin.min;
          rangeMax.value = rangeMax.max;
          inputMin.value = rangeMin.min;
          inputMax.value = rangeMax.max;

          // Force update of the track
          section.querySelector('.input-min')
                .dispatchEvent(new Event('input', { bubbles: true }));
        });
      });
    }


document.querySelectorAll('.catalog_item').forEach((item, index) => {
  const swiperEl = item.querySelector('.catalogSwiper');
  const paginationEl = item.querySelector('.swiper-pagination');

  const swiper = new Swiper(swiperEl, {
    slidesPerView: 1,
    slidesPerGroup: 1,
    nested: true,
    // loop: true,
    autoplay: {
      delay:900,
      disableOnInteraction: false
    },
    pagination: {
      el: paginationEl,
      clickable: true
    }
  });

  // stop autoplay immediately (so it won't play by default)
  swiper.autoplay.stop();

  // start autoplay when hovered
  swiperEl.addEventListener('mouseenter', () => {
    swiper.autoplay.start();
  });

  // stop autoplay when not hovered
  swiperEl.addEventListener('mouseleave', () => {
    swiper.autoplay.stop();
  });
});




    // application modal
    const openButtons = document.querySelectorAll('.open-modal-btn');
    const modalOverlay = document.getElementById('modalOverlay');
    const modalBox = document.getElementById('modalBox');
    const closeBtn = document.getElementById('closeModal');

    // Loop through all open buttons
    openButtons.forEach(button => {
      button.addEventListener('click', () => {
        modalOverlay.classList.add('active');
        modalBox.classList.add('active');
      });
    });

    // Close modal on X click
    closeBtn.addEventListener('click', () => {
      modalOverlay.classList.remove('active');
      modalBox.classList.remove('active');
    });

    // Close modal when clicking outside
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) {
        modalOverlay.classList.remove('active');
        modalBox.classList.remove('active');
      }
    });


    // application modal
    const openButtons1 = document.querySelectorAll('.open-search-modal-btn');
    const modalOverlay1 = document.getElementById('modalOverlay1');
    const modalBox1 = document.getElementById('modalBox1');
    const closeBtn1 = document.getElementById('closeModal1');

    // Loop through all open buttons
    openButtons1.forEach(button => {
      button.addEventListener('click', () => {
        modalOverlay1.classList.add('active');
        modalBox1.classList.add('active');
      });
    });

    // Close modal on X click
    closeBtn1.addEventListener('click', () => {
      modalOverlay1.classList.remove('active');
      modalBox1.classList.remove('active');
    });

    // Close modal when clicking outside
    modalOverlay1.addEventListener('click', (e) => {
      if (e.target === modalOverlay1) {
        modalOverlay1.classList.remove('active');
        modalBox1.classList.remove('active');
      }
    });

      // category button in catalog
      const categoryBtn = document.getElementById("category_open_button");
      const categoryList = document.getElementById("category_list");
      

      if(categoryList && categoryList){
        const categoryItems = categoryList.querySelectorAll(".list_item");
        // Toggle open class on button click
        categoryBtn.addEventListener("click", () => {
          categoryList.classList.toggle("open");
        });

        // Handle li clicks
        categoryItems.forEach(item => {
          item.addEventListener("click", () => {
            // remove active from all
            categoryItems.forEach(li => li.classList.remove("active"));
            // add active to clicked one
            item.classList.add("active");

            // set button text to clicked item's text
            categoryBtn.textContent = item.textContent;

            // close list after selection
            categoryList.classList.remove("open");
          });
        });
      }




      // filter button
    const filterBtn = document.querySelector(".filter_button");
    const filterPanel = document.querySelector(".filter_main");
    const closeBtn2 = document.querySelector(".filter_main .close");
    const overlay = document.querySelector(".filter-overlay");

    if (filterBtn && filterPanel && closeBtn2 && overlay) {
      function openFilter() {
        filterPanel.classList.add("active");
        overlay.classList.add("active");
      }

      function closeFilter() {
        filterPanel.classList.remove("active");
        overlay.classList.remove("active");
      }

      filterBtn.addEventListener("click", openFilter);
      closeBtn2.addEventListener("click", closeFilter);
      overlay.addEventListener("click", closeFilter);
    }


    ymaps.ready(init);

    function init() {

        const officeCoords = (window.MAP_CFG && MAP_CFG.office)
            ? [Number(MAP_CFG.office.lat), Number(MAP_CFG.office.lng)]
            : [55.755348, 37.759533];

        const warehouseCoords = (window.MAP_CFG && MAP_CFG.warehouse)
            ? [Number(MAP_CFG.warehouse.lat), Number(MAP_CFG.warehouse.lng)]
            : [55.748838, 37.757386];

        // Проверяем наличие #map1
        const map1Container = document.getElementById("map1");
        if (map1Container) {
            const map1 = new ymaps.Map(map1Container, {
                center: officeCoords,
                zoom: 11,
                controls: []
            });

            const officePlacemark = new ymaps.Placemark(
                officeCoords,
                { balloonContent: "<b>Адрес офиса магазина</b><br>г. Москва, ул. Плеханова д.7" },
                {
                    iconLayout: 'default#imageWithContent',
                    iconImageHref: 'assets/images/map_icon.png',
                    iconImageSize: [28, 38],
                    iconImageOffset: [-14, -38]
                }
            );

            map1.geoObjects.add(officePlacemark);
        }

        // Проверяем наличие #map2
        const map2Container = document.getElementById("map2");
        if (map2Container) {
            const map2 = new ymaps.Map(map2Container, {
                center: warehouseCoords,
                zoom: 13,
                controls: []
            });

            const warehousePlacemark = new ymaps.Placemark(
                warehouseCoords,
                { balloonContent: "<b>Адрес склада</b><br>г. Москва, ул. Электродная 13С2А" },
                {
                    iconLayout: 'default#imageWithContent',
                    iconImageHref: 'assets/images/map_icon.png',
                    iconImageSize: [28, 38],
                    iconImageOffset: [-14, -38]
                }
            );

            map2.geoObjects.add(warehousePlacemark);
        }
    }

});



// Plus va minus buttonlar tashqariga chiqarildi
// Quantity buttons
// document.querySelectorAll('.catalog_item').forEach(item => {
//     const qtyEl = item.querySelector('.qty');
//     const plusBtn = item.querySelector('.plus');
//     const minusBtn = item.querySelector('.minus');
//
//     // Plus button
//     plusBtn.addEventListener('click', () => {
//         let current = parseInt(qtyEl.value) || 0;
//         if (current < 1000) {
//             qtyEl.value = current + 1;
//         }
//     });
//
//     // Minus button
//     minusBtn.addEventListener('click', () => {
//         let current = parseInt(qtyEl.value) || 1;
//         if (current > 1) {
//             qtyEl.value = current - 1;
//         }
//     });
//
//     // Prevent typing more than 1000
//     qtyEl.addEventListener('input', () => {
//         let current = parseInt(qtyEl.value) || 1;
//         if (current > 1000) {
//             qtyEl.value = 1000;
//         } else if (current < 1) {
//             qtyEl.value = 1;
//         }
//     });
// });


// Quantity buttons (IDs only)
const qtyEl = document.getElementById('qty');
const plusBtn = document.getElementById('plus');
const minusBtn = document.getElementById('minus');

if (qtyEl && plusBtn && minusBtn) {
    // Plus button
    plusBtn.addEventListener('click', () => {
        console.log('Plus button clicked');
        let current = parseInt(qtyEl.value) || 0;
        if (current < 1000) {
            qtyEl.value = current + 1;
        }
    });

    // Minus button
    minusBtn.addEventListener('click', () => {
        let current = parseInt(qtyEl.value) || 1;
        if (current > 1) {
            qtyEl.value = current - 1;
        }
    });

    // Restrict manual typing
    qtyEl.addEventListener('input', () => {
        let val = parseInt(qtyEl.value) || 1;
        if (val > 1000) val = 1000;
        if (val < 1) val = 1;
        qtyEl.value = val;
    });
}




// // profile password show/off
// document.querySelectorAll(".toggle-password").forEach(icon => {
//     // check class before running
//     if (icon.classList.contains("toggle-password")) {
//         icon.addEventListener("click", () => {
//             const input = icon.previousElementSibling;
//             if (input && input.classList.contains("password-input")) {
//                 if (input.type === "password") {
//                     input.type = "text";
//                     icon.textContent = "visibility"; // open eye
//                 } else {
//                     input.type = "password";
//                     icon.textContent = "visibility_off"; // crossed eye
//                 }
//             }
//         });
//     }
// });
//
//
// // login/register (all modals related to the profile)
//
//
// // Open modal
// document.querySelectorAll('.open-modal-btn-profile').forEach(btn => {
//     btn.addEventListener('click', () => {
//         const modalId = btn.getAttribute('data-modal');
//         const modal = document.getElementById(modalId);
//
//
//         if (modal) {
//             // Close any already open modals
//             document.querySelectorAll('.modal-overlay-profile.active')
//                 .forEach(openModal => openModal.classList.remove('active'));
//
//
//             // Open the selected modal
//             modal.classList.add('active');
//         }
//     });
// });
//
//
// // Close modal (X button)
// document.querySelectorAll('.modal-overlay-profile .close-btn').forEach(btn => {
//     btn.addEventListener('click', () => {
//         const modal = btn.closest('.modal-overlay-profile');
//         modal.classList.remove('active');
//     });
// });
//
//
// // Close modal when clicking outside modal box
// document.querySelectorAll('.modal-overlay-profile').forEach(overlay => {
//     overlay.addEventListener('click', (e) => {
//         if (e.target === overlay) {
//             overlay.classList.remove('active');
//         }
//     });
// });

document.addEventListener('click', function(e){
    const openBtn  = e.target.closest('.open-modal-btn-profile');
    if (openBtn) {
        const id = openBtn.getAttribute('data-modal');
        const ov = document.getElementById(id);
        if (ov) {
            document.querySelectorAll('.modal-overlay-profile.active').forEach(m=>m.classList.remove('active'));
            ov.classList.add('active');
        }
        e.preventDefault();
        return;
    }

    const closeBtn = e.target.closest('.modal-overlay-profile .close-btn');
    if (closeBtn) {
        closeBtn.closest('.modal-overlay-profile')?.classList.remove('active');
        return;
    }

    if (e.target.classList.contains('modal-overlay-profile')) {
        e.target.classList.remove('active');
        return;
    }

    // Agar parol ko‘rsatish kerak bo‘lsa (xohlamasangiz olib tashlang):
    const tgl = e.target.closest('.toggle-password');
    if (tgl) {
        const inp = tgl.previousElementSibling;
        if (inp && inp.classList.contains('password-input')) {
            inp.type = (inp.type === 'password') ? 'text' : 'password';
            tgl.textContent = (inp.type === 'password') ? 'visibility_off' : 'visibility';
        }
        return;
    }
});







window.openTab = function(tabName, el) {
    // Find the closest modal overlay (so we only affect that modal)
    const modal = el.closest('.modal-overlay-profile');


    if (!modal) return;


    // remove active from tab items, blogs, and forms inside this modal only
    modal.querySelectorAll('.tab_item').forEach(item => {
        item.classList.remove('active');
    });
    modal.querySelectorAll('.tab_blog').forEach(blog => {
        blog.classList.remove('active');
    });
    modal.querySelectorAll('.form_item').forEach(form => {
        form.classList.remove('active');
    });


    // activate the selected tab, blog, and form inside this modal
    modal.querySelector('.tab_item.' + tabName).classList.add('active');
    modal.querySelector('.' + tabName + '_blog').classList.add('active');
    modal.querySelector('.form-' + tabName).classList.add('active');
};



// get all search blocks
const searchBlocks = document.querySelectorAll('.header_search');

searchBlocks.forEach(block => {
    const searchInput = block.querySelector('.search_input'); // input
    const suggestions = block.querySelector('.search_suggestions'); // dropdown

    if (!searchInput || !suggestions) return; // skip if missing

    searchInput.addEventListener('input', () => {
        if (searchInput.value.trim().length > 0) {
            suggestions.style.display = 'block';
        } else {
            suggestions.style.display = 'none';
        }
    });
});

// hide suggestions when clicking outside
document.addEventListener('click', (e) => {
    searchBlocks.forEach(block => {
        if (!block.contains(e.target)) {
            const suggestions = block.querySelector('.search_suggestions');
            if (suggestions) suggestions.classList.remove('open');
        }
    });
});
