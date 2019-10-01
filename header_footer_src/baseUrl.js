const hostname = window.location.hostname

let baseUrl = ""
switch (hostname) {
    case "localhost":
        baseUrl = "http://dev.patentsview.org"
        break
    case "dev.patentsview.org":
        baseUrl = "http://dev.patentsview.org"
        break
    case "www.patentsview.org":
        baseUrl = "http://www.patentsview.org"
        break
    default:
        console.log("Unknown hostname.")
}

export default baseUrl
