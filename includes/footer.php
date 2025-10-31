<script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('/LacakPro/sw.js')
            .then(reg => console.log('Service Worker LacakPro terdaftar!'))
            .catch(err => console.log('Service Worker gagal terdaftar: ', err));
        });
      }
    </script>
    </body> 
</html>