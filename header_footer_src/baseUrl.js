const hostname = window.location.hostname

let baseUrl = ""
switch (hostname) {
    case "localhost":
        baseUrl = "https://dev.patentssview.org"
        break
    case "dev.patentsview.org":
        baseUrl = "https://dev.patentssview.org"
        break
    case "www.patentsview.org":
        baseUrl = "https://www.patentssview.org"
        break
    default:
        console.log("Unknown hostname.")
}

export default baseUrl
