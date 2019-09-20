import Vue from "vue"
import Footer from "./footer"

const footer = document.createElement("div")
document.body.appendChild(footer)

new Vue({
    el: footer,
    render: h => h(Footer)
})
