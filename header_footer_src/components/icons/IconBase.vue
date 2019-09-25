<template>
  <svg xmlns="http://www.w3.org/2000/svg" 
    :width="width" 
    :height="height" 
    :viewBox="viewBox" 
    :aria-labelledby="iconName" 
    role="presentation"
    :fill="color"
    :stroke="stroke"
  >
    <title :id="iconName" lang="en">{{iconName}} icon</title>
    <slot />
  </svg>
</template>

<script>
export default {
  props: {
    iconName: {
      type: String,
      default: 'box'
    },
    width: {
      type: [Number, String],
      default: 24
    },
    height: {
      type: [Number, String],
      default: 18
    },
    color: {
      type: String,
      default: '#9cabb9'
    },
    stroke: {
      type: String,
      default: '#9cabb9'
    },
    viewBox: {
      type: String,
      default: "50 -30 400 400"
    }
  }
}
</script>

<style scoped>
svg {
  display: inline-block;
  vertical-align: middle;
  margin-right: 5px;
}
</style>