@php
  $containerFooter =
      isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
          ? 'container-xxl'
          : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body">
        &#169;
        <script>
          document.write(new Date().getFullYear());
        </script>
        , Tim Teknis WR4 Bidang Data dan Teknologi Informasi
      </div>
      <div class="d-none d-lg-inline-block">
        &nbsp;
      </div>
    </div>
  </div>
</footer>
<!-- / Footer -->
