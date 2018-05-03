<template>
  <footer id="footer" class="text-muted" :style="{ marginTop: mgtop + 'em' }">
    <div class="container">
      <p class="float-right">
        <a href="#app">Back to top</a>
      </p>
      <p>This app is &copy; Ahsanulks</p>
      <p>Theme by <a href="../../">bootstrap</a></p>
    </div>
  </footer>
</template>

<script>
export default {
  data() {
    return {
      mgtop: 3
    }
  },
}
</script>
