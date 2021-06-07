<template>
  <img
    class="mpsc-logo"
    width="210"
    height="210"
    :src="appLogoImage"
    alt="mpsc-logo"
  >
</template>

<script>
import { $themeConfig } from '@themeConfig'

export default {
  setup() {
    const { appLogoImage } = $themeConfig.app

    return {
      appLogoImage,
    }
  },
}

</script>

<style lang="scss">
.mpsc-logo {
  height: 50px;
  width: 50px;
}
</style>
