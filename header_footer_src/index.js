import Vue from "vue"
import Header from "./components/header.vue"
import Footer from "./components/footer"

const header = document.createElement("div")
document.body.prepend(header)

new Vue({
    el: header,
    render: h => h(Header)
})

const footer = document.createElement("div")
document.body.append(footer)

new Vue({
    el: footer,
    render: h => h(Footer)
})
