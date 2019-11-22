import Vue from "vue"
import Header from "./components/Header"
import Footer from "./components/Footer"

// temporary banner
import SSHNotif from './components/SSHNotif'

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

const sshNotif = document.createElement("div")
document.body.append(sshNotif)

new Vue({
    el: sshNotif,
    render: h => h(SSHNotif)
})