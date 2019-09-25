<template>
    <div>
        <header>
            <div>
                <h1>
                    <a :href="baseUrl + '/web/'" title="PatentsView - USPTO">
                        <img
                            alt="PatentsView Logo"
                            :src="pv_logo"
                            style="width: 221px; margin-top: 6px;"
                        />
                    </a>
                </h1>
                <nav>
                    <ul>
                        <li>
                            <a class="nav-label" :href="baseUrl + '/web/#viz/relationships'">
                                <icon-base icon-name="Network">
                                    <Network />
                                </icon-base>Relationships
                            </a>
                        </li>
                        <li class>
                            <a class="nav-label" :href="baseUrl + '/web/#viz/locations'">
                                <icon-base icon-name="Location">
                                    <Location />
                                </icon-base>Locations
                            </a>
                        </li>
                        <li class>
                            <a class="nav-label" :href="baseUrl + '/web/#viz/comparisons'">
                                <icon-base icon-name="Compare">
                                    <Compare />
                                </icon-base>Comparisons
                            </a>
                        </li>
                        <li class>
                            <a class="nav-label" :href="baseUrl + '/web/#search&amp;simp=1'">
                                <icon-base icon-name="Search">
                                    <Search />
                                </icon-base>List Search
                            </a>
                        </li>
                        <li class="data-source-nav nav-dropdown">
                            <div
                                class="nav-label"
                                @mouseenter="showDropdown = true"
                                @mouseleave="initiateHide"
                            >Data &amp; Community</div>
                            <span
                                v-if="showDropdown"
                                @mouseenter="checkEl"
                                @mouseleave="showDropdown = false"
                                class="header-dropdown"
                            >
                                <ul>
                                    <li>
                                        <a :href="baseUrl + '/api'">
                                            <h5>
                                                <icon-base icon-name="API" :width="dropdownSvgWidth" :height="dropdownSvgHeight" :view-box="dropdownSvgViewbox">
                                                    <API />
                                                </icon-base>
                                                Api
                                            </h5>
                                            <p>provides developers and researchers programmatic access to the underlying data</p>
                                        </a>
                                    </li>
                                    <li>
                                        <a :href="baseUrl + '/query'">
                                            <h5>
                                                <icon-base icon-name="Query" :width="dropdownSvgWidth" :height="dropdownSvgHeight" :view-box="dropdownSvgViewbox">
                                                    <Query />
                                                </icon-base>
                                                Data Query

                                            </h5>
                                            <p>provides a graphical interface for researchers to query the entire underlying database</p>
                                        </a>
                                    </li>
                                    <li>
                                        <a :href="baseUrl + '/download'">
                                            <h5>
                                                <icon-base icon-name="Download" :width="dropdownSvgWidth" :height="dropdownSvgHeight" :view-box="dropdownSvgViewbox">
                                                    <Download />
                                                </icon-base>
                                                Data Download

                                            </h5>
                                            <p>provides downloadable tables as csv files covering the underlying database</p>
                                        </a>
                                    </li>
                                    <li>
                                        <a :href="baseUrl + '/community'">
                                            <h5>
                                                <icon-base icon-name="Community" :width="dropdownSvgWidth" :height="dropdownSvgHeight" :view-box="dropdownSvgViewbox">
                                                    <Community />
                                                </icon-base>
                                                Community

                                            </h5>
                                            <p>provides news, updates, and a forum for discussion</p>
                                        </a>
                                    </li>
                                </ul>
                            </span>
                        </li>
                    </ul>
                </nav>
            </div>
        </header>
    </div>
</template>

<script>
import baseUrl from '../baseUrl'
import pv_logo from '../img/pv-header-logo.png'
import IconBase from './icons/IconBase.vue'
import Network from './icons/svgs/network.vue'
import Compare from './icons/svgs/compare.vue'
import Location from './icons/svgs/location.vue'
import Search from './icons/svgs/search.vue'
import API from './icons/svgs/api.vue'
import Download from './icons/svgs/download.vue'
import Query from './icons/svgs/query.vue'
import Community from './icons/svgs/community.vue'

export default {
    components: {
        IconBase,
        Network,
        Compare,
        Location,
        Search,
        API,
        Download,
        Query,
        Community
    },
    data() {
        return {
            baseUrl: '',
            pv_logo: '',
            dropdownSvgWidth: 10,
            dropdownSvgHeight: 10,
            dropdownSvgViewbox: "0 0 32 32",
            showDropdown: false,
            timeout: null
        }
    },
    mounted() {
        this.baseUrl = baseUrl
        this.pv_logo = pv_logo
    },
    methods: {
        checkEl(e) {
            if (e.srcElement.className === 'header-dropdown') {
                clearTimeout(this.timeout)
            }
        },
        initiateHide(e) {
            this.timeout = setTimeout(() => {
                this.showDropdown = false
            }, 100)
        }
    }
}
</script>

<style scoped>
header nav ul {
    margin-bottom: 0;
}

header .header-dropdown svg {
    left: -15px;
    position: absolute;
}
header nav ul li a {
    color: #9cabb9;
}

header .nav-label {
    padding-top: 23px !important;
}

header a:hover {
    font-weight: 300;
}

header {
    margin: 0;
    padding: 0;
    border: 0;
    font-size: 100%;
    font: inherit;
    vertical-align: baseline;
}

header {
    display: block;
}

header {
    display: block;
}

header.med-header,
header.thin-header {
    color: #4f5f6f;
    font-family: Open Sans, sans-serif;
    font-size: 12px;
}

header.med-header .api a:before,
header.thin-header .api a:before {
    text-indent: -119988px;
    overflow: hidden;
    text-align: left;
    background-position: sprite-position(sprite-map("shared/*.png"), api, 0, 0);
    height: image-height(sprite-file(sprite-map("shared/*.png"), api));
    width: image-width(sprite-file(sprite-map("shared/*.png"), api));
    background-image: sprite-map("shared/*.png");
    background-repeat: no-repeat;
}
header.med-header .api a.api-hover:before,
header.med-header .api a.api_hover:before,
header.med-header .api a:hover:before,
header.thin-header .api a.api-hover:before,
header.thin-header .api a.api_hover:before,
header.thin-header .api a:hover:before {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "api_hover",
        0,
        0
    );
}
header.med-header .api a.api-target:before,
header.med-header .api a.api_target:before,
header.med-header .api a:target:before,
header.thin-header .api a.api-target:before,
header.thin-header .api a.api_target:before,
header.thin-header .api a:target:before {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "api_target",
        0,
        0
    );
}
header.med-header .api a.api-active:before,
header.med-header .api a.api_active:before,
header.med-header .api a:active:before,
header.thin-header .api a.api-active:before,
header.thin-header .api a.api_active:before,
header.thin-header .api a:active:before {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "api_active",
        0,
        0
    );
}
header.med-header .data-query a:before,
header.thin-header .data-query a:before {
    text-indent: -119988px;
    overflow: hidden;
    text-align: left;
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        data-query,
        0,
        0
    );
    height: image-height(sprite-file(sprite-map("shared/*.png"), data-query));
    width: image-width(sprite-file(sprite-map("shared/*.png"), data-query));
    background-image: sprite-map("shared/*.png");
    background-repeat: no-repeat;
}
header.med-header .data-query a.data-query-hover:before,
header.med-header .data-query a.data-query_hover:before,
header.med-header .data-query a:hover:before,
header.thin-header .data-query a.data-query-hover:before,
header.thin-header .data-query a.data-query_hover:before,
header.thin-header .data-query a:hover:before {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "data-query_hover",
        0,
        0
    );
}
header.med-header .data-query a.data-query-target:before,
header.med-header .data-query a.data-query_target:before,
header.med-header .data-query a:target:before,
header.thin-header .data-query a.data-query-target:before,
header.thin-header .data-query a.data-query_target:before,
header.thin-header .data-query a:target:before {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "data-query_target",
        0,
        0
    );
}
header.med-header .data-query a.data-query-active:before,
header.med-header .data-query a.data-query_active:before,
header.med-header .data-query a:active:before,
header.thin-header .data-query a.data-query-active:before,
header.thin-header .data-query a.data-query_active:before,
header.thin-header .data-query a:active:before {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "data-query_active",
        0,
        0
    );
}
header.med-header .data-download a:before,
header.thin-header .data-download a:before {
    text-indent: -119988px;
    overflow: hidden;
    text-align: left;
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        data-download,
        0,
        0
    );
    height: image-height(
        sprite-file(sprite-map("shared/*.png"), data-download)
    );
    width: image-width(sprite-file(sprite-map("shared/*.png"), data-download));
    background-image: sprite-map("shared/*.png");
    background-repeat: no-repeat;
}
header.med-header .data-download a.data-download-hover:before,
header.med-header .data-download a.data-download_hover:before,
header.med-header .data-download a:hover:before,
header.thin-header .data-download a.data-download-hover:before,
header.thin-header .data-download a.data-download_hover:before,
header.thin-header .data-download a:hover:before {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "data-download_hover",
        0,
        0
    );
}
header.med-header .data-download a.data-download-target:before,
header.med-header .data-download a.data-download_target:before,
header.med-header .data-download a:target:before,
header.thin-header .data-download a.data-download-target:before,
header.thin-header .data-download a.data-download_target:before,
header.thin-header .data-download a:target:before {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "data-download_target",
        0,
        0
    );
}
header.med-header .data-download a.data-download-active:before,
header.med-header .data-download a.data-download_active:before,
header.med-header .data-download a:active:before,
header.thin-header .data-download a.data-download-active:before,
header.thin-header .data-download a.data-download_active:before,
header.thin-header .data-download a:active:before {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "data-download_active",
        0,
        0
    );
}
header.med-header .logo {
    text-indent: -119988px;
    overflow: hidden;
    text-align: left;
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        med-logo,
        0,
        0
    );
    height: image-height(sprite-file(sprite-map("shared/*.png"), med-logo));
    width: image-width(sprite-file(sprite-map("shared/*.png"), med-logo));
    background-image: sprite-map("shared/*.png");
    background-repeat: no-repeat;
}
header.med-header .logo:hover,
header.med-header .med-logo-hover.logo,
header.med-header .med-logo_hover.logo {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "med-logo_hover",
        0,
        0
    );
}
header.med-header .logo:target,
header.med-header .med-logo-target.logo,
header.med-header .med-logo_target.logo {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "med-logo_target",
        0,
        0
    );
}
header.med-header .logo:active,
header.med-header .med-logo-active.logo,
header.med-header .med-logo_active.logo {
    background-position: sprite-position(
        sprite-map("shared/*.png"),
        "med-logo_active",
        0,
        0
    );
}

header .nav-dropdown ul h5,
header nav > ul > li > .nav-label,
header nav > ul > li > a {
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.search header h1,
header .search h1 {
    float: left;
    width: 21.2183%;
}

.search header nav,
.search nav,
.search section,
header .search nav {
    float: right;
    width: 76.3452%;
}

header {
    background: #4f5f6f;
    color: #fff;
    height: 61px;
    width: 100%;
    z-index: 1000;
}
.search header aside,
.search header h1,
header .column-left,
header .search aside,
header .search h1,
header h1 {
    float: left;
    width: 21.2183%;
}
.search header nav,
.search header section,
header .column-right,
header .search nav,
header .search section,
header nav {
    float: right;
    width: 76.3452%;
}
header h1 {
    margin-top: 3px;
}
header > div {
    margin: 0 auto;
    width: 985px;
}
header nav > ul {
    display: inline-block;
    float: right;
}
header nav > ul > li {
    display: block;
    float: left;
    outline: 0;
    color: #9cabb9;
    background: #4f5f6f;
    transition: all 0.2s ease-in-out;
}
header nav > ul > li:nth-child(4) {
    min-width: 147px;
}
header nav > ul > li:nth-child(5) {
    min-width: 115px;
}
header nav > ul > li > .nav-label,
header nav > ul > li > a {
    text-align: center;
    text-decoration: none;
    font-size: 10px;
    font-size: 1rem;
    font-family: Open Sans, sans-serif;
    display: block;
    outline: 0;
    box-sizing: border-box;
    height: 61px;
    padding: 0 10px 0 25px;
    position: relative;
    font-weight: 300;
}
header nav > ul > li > .nav-label svg.icon,
header nav > ul > li > a svg.icon {
    position: absolute;
    left: 12px;
    top: 22px;
    width: 2.4em;
    height: 1.8em;
    fill: #9cabb9;
    stroke: #9cabb9;
    transition: all 0.2s ease-in-out;
}

header nav > ul > li > a:hover {
    color: #e7e3b9;
}

header nav > ul > li:hover {
    color: #e7e3b9;
}

header nav > ul > li:hover svg {
    fill: #e7e3b9;
    stroke: #e7e3b9;
}
header nav > ul > li.active {
    color: #e7e3b9;
    background: #445160;
}
header nav > ul > li.active svg.icon {
    fill: #e7e3b9;
    stroke: #e7e3b9;
}
header .nav-dropdown {
    position: relative;
}
header .nav-dropdown:before {
    content: "";
    display: block;
    position: absolute;
    top: 20px;
    left: 0;
    height: 20px;
    border-left: 1px solid #9cabb9;
    z-index: 1;
}
header .nav-dropdown:after {
    border: 1px solid transparent;
    border-left-color: #9cabb9;
    border-bottom-color: #9cabb9;
}
header .nav-dropdown:after,
header .nav-dropdown:hover:after {
    content: "";
    display: block;
    height: 5px;
    width: 5px;
    top: 27px;
    right: 3px;
    position: absolute;
    -webkit-transform: rotate(-45deg);
    transform: rotate(-45deg);
}
header .nav-dropdown:hover:after {
    border: 1px solid transparent;
    border-left-color: #e7e3b9;
    border-bottom-color: #e7e3b9;
}
header .nav-dropdown .nav-label {
    font-size: 8px;
    font-size: 0.8rem;
    padding-left: 18px;
    transform: translateY(3px);
}
header .nav-dropdown ul {
    z-index: 1000;
    position: absolute;
    top: 100%;
    right: 0;
    display: block;
}
header .nav-dropdown ul li {
    font-weight: 300;
}
header .nav-dropdown ul li a,
header .nav-dropdown ul li a:visited {
    text-decoration: none;
}
header .nav-dropdown ul li a {
    position: relative;
    display: block;
    box-sizing: border-box;
    width: 262px;
    padding: 19px 20px 18px 38px;
    background: #4f5f6f;
    transition: all 0.25s ease-in-out;
}
header .nav-dropdown ul li a:not(:first-child) {
    border-top: 1px solid #677788;
}
header .nav-dropdown ul li a:hover {
    background: #445160;
}
header .nav-dropdown ul li h5 {
    position: relative;
}
header .nav-dropdown ul li svg.icon {
    fill: #e7e3b9;
    position: absolute;
    left: -35px;
    top: 1px;
    width: 4em;
}
header .nav-dropdown ul h5 {
    color: #e7e3b9;
    font-size: 10px;
    font-size: 1rem;
    font-family: Open Sans, sans-serif;
    text-decoration: underline;
    margin-bottom: 6px;
}
header .nav-dropdown ul p {
    text-decoration: none;
    color: #f0f0f0;
    font-size: 11px;
    font-size: 1.1rem;
    line-height: 15px;
    line-height: 1.36364;
    font-family: Open Sans, sans-serif;
}

@media (min-width: 1013px) {
    .centered-header {
        width: 985px;
    }
}
@media (max-width: 1012px) {
    .centered-header {
        width: 758px;
    }
}
header.med-header .centered-header,
header.thin-header .centered-header {
    margin: 0 auto;
    padding: 0 14px;
}
header.med-header .centered-header:after,
header.thin-header .centered-header:after {
    display: block;
    content: "";
    clear: both;
}
header.med-header a,
header.med-header a:visited,
header.thin-header a,
header.thin-header a:visited {
    color: #d0dded;
    text-decoration: none;
    font-size: 11px;
}
header.med-header ul,
header.thin-header ul {
    float: right;
}
header.med-header ul li,
header.thin-header ul li {
    float: left;
    position: relative;
}
header.med-header ul li a,
header.thin-header ul li a {
    display: block;
    padding: 7px 17px 6px 13px;
    transition: all 0.25s ease-in-out;
}
header.med-header ul li a:before,
header.thin-header ul li a:before {
    content: ".";
    display: inline-block;
    margin: 1px 2px 0 -1px;
    vertical-align: middle;
}
header.med-header ul li.active,
header.thin-header ul li.active {
    background: #445160;
}
header.med-header ul li:after,
header.thin-header ul li:after {
    display: block;
    content: "";
    clear: both;
}
header.med-header ul li .nav-tooltip,
header.thin-header ul li .nav-tooltip {
    visibility: hidden;
    opacity: 0;
    -webkit-transition: all 0.2s ease-in-out;
    -webkit-transition-delay: 0s;
    transition: all 0.2s ease-in-out 0s;
    z-index: 100;
    position: absolute;
    top: 100%;
    left: 0;
    left: calc(50% - 80px);
    background: #f6f6f7;
    width: 134px;
    padding: 12px;
    font-size: 11px;
    line-height: 13px;
    border: 1px solid #ddd;
    border-radius: 2px;
}
header.med-header ul li .nav-tooltip:before,
header.thin-header ul li .nav-tooltip:before {
    content: "";
    display: block;
    position: absolute;
    top: -8px;
    left: 50%;
    left: calc(50% - 4px);
    border: 4px solid transparent;
    border-bottom-color: #f6f6f7;
}
header.med-header ul li:hover .nav-tooltip,
header.thin-header ul li:hover .nav-tooltip {
    visibility: visible;
    opacity: 1;
    top: 100%;
    top: calc(100% - 4px);
    -webkit-transition: all 0.2s ease-in-out;
    -webkit-transition-delay: 0.5s;
    transition: all 0.2s ease-in-out 0.5s;
}
header.med-header ul li:not(.active) + li:not(.active):before,
header.thin-header ul li:not(.active) + li:not(.active):before {
    content: "";
    display: block;
    height: 14px;
    position: absolute;
    left: 0;
    top: 23px;
    border-left: 1px solid #3c4d5f;
}
header.med-header ul li:hover a,
header.thin-header ul li:hover a {
    color: #e7e3b9;
}
header.med-header .data-download a:before,
header.thin-header .data-download a:before {
    height: 24px;
}
header.thin-header {
    height: 38px;
    background: #445160;
}
@media (min-width: 985px) {
    header.thin-header .centered-header {
        width: 985px;
    }
}
header.thin-header ul li:not(.active) + li:not(.active):before {
    top: 12px;
    border-left-color: #515e6e;
}
header.thin-header .logo {
    display: none;
}
header.med-header {
    height: 61px;
    background: #4f5f6f;
}
header.med-header ul li a {
    padding-top: 18px;
    padding-bottom: 18px;
}
header.med-header .logo {
    float: left;
    content: "";
    display: block;
    margin: 10px 0 0;
}
</style>>