<template>
    <div>
        <footer class="container-fluid fat-footer primary-footer">
            <div class="row">
                <div class="center-shell">
                    <div class="column one">
                        <div class="new-info">
                            <h4>PatentsView Info</h4>
                            <ul>
                                <li class="relationships">
                                    <a
                                        :href="baseUrl + '/web/#viz/relationships'"
                                        title="Relationships"
                                    >Relationships</a>
                                </li>
                                <li class="locations-link">
                                    <a
                                        :href="baseUrl + '/web/#viz/locations'"
                                        title="Locations"
                                    >Locations</a>
                                </li>
                                <li class="comparisons">
                                    <a
                                        :href="baseUrl + '/web/#viz/comparisons'"
                                        title="Comparisons"
                                    >Comparisons</a>
                                </li>
                                <li class="list-search">
                                    <a
                                        :href="baseUrl + '/web/#search&amp;simp=1'"
                                        title="List Search"
                                    >List Search</a>
                                </li>
                                <li class="about">
                                    <a @click="modalIsShow = true" title="About">About</a>
                                </li>
                                <li class="methods">
                                    <a
                                        :href="baseUrl + '/web/#'"
                                        title="Methods and Sources"
                                    >Methods and Sources</a>
                                </li>
                                <li class="glossary">
                                    <a
                                        :href="baseUrl + '/api/glossary.html'"
                                        title="Glossary"
                                    >Glossary</a>
                                </li>
                                <li class="feedback">
                                    <a
                                        href="mailto:contact@patentsview.org"
                                        title="Send Feedback"
                                    >Send Feedback</a>
                                </li>
                            </ul>
                        </div>
                        <div class="copyright">
                            <!-- react-text: 147 -->
                            © Copyright
                            <!-- /react-text -->
                            <!-- react-text: 148 -->
                            <!-- /react-text -->
                            <!-- react-text: 149 -->
                            2019
                            <!-- /react-text -->
                        </div>
                    </div>
                    <div class="column two">
                        <div class="new-data-sources">
                            <h4>Data Sources</h4>
                            <ul>
                                <li>
                                    <a :href="baseUrl + '/api'">Api</a>
                                </li>
                                <li>
                                    <a :href="baseUrl + '/query'">Data Query</a>
                                </li>
                                <li>
                                    <a :href="baseUrl + '/download'">Data Download</a>
                                </li>
                                <li>
                                    <a :href="baseUrl + '/community'">Community</a>
                                </li>
                            </ul>
                            <div class="update-date">
                                <!-- react-text: 163 -->
                                Data as of
                                <!-- /react-text -->
                                <!-- react-text: 164 -->
                                March 12, 2019
                                <!-- /react-text -->
                            </div>
                        </div>
                    </div>
                    <div class="column three">
                        <div class="new-credit">
                            <h4>This Initiative</h4>
                            <p>This platform uses data derived from the US Patent and Trademark Office (USPTO) bulk data files. These data are provided for research purposes and do not constitute the official USPTO record.</p>
                            <p>
                                <!-- react-text: 170 -->
                                Supported by the
                                <!-- /react-text -->
                                <!-- react-text: 171 -->
                                <!-- /react-text -->
                                <a
                                    href="http://www.uspto.gov/economics/"
                                    target="_blank"
                                >USPTO Office of the Chief Economist</a>
                                <!-- react-text: 173 -->
                                , with additional support from the
                                <!-- /react-text -->
                                <!-- react-text: 174 -->
                                <!-- /react-text -->
                                <a
                                    href="http://www.usda.gov"
                                    target="_blank"
                                >US Department of Agriculture</a>
                                <!-- react-text: 176 -->
                                <!-- /react-text -->
                                <!-- react-text: 177 -->
                                (USDA).
                                <!-- /react-text -->
                            </p>
                        </div>
                        <div class="logo-link">
                            <a href="http://www.uspto.gov/" target="_blank">
                                <img
                                    class="logo-img"
                                    alt="sponsored by the USPTO"
                                    :src="uspto_logo"
                                />
                            </a>
                        </div>
                    </div>
                    <div class="column four">
                        <div class="terms-and-services">
                            <h4>Terms &amp; Services</h4>
                            <p>
                                <!-- react-text: 185 -->
                                Users are free to use, share, or adapt the material for any purpose, subject to the standards of the
                                <!-- /react-text -->
                                <!-- react-text: 186 -->
                                <!-- /react-text -->
                                <a
                                    href="https://creativecommons.org/licenses/by/4.0/"
                                    target="_blank"
                                >Creative Commons Attribution 4.0 International License</a>
                                <!-- react-text: 188 -->
                                .
                                <!-- /react-text -->
                            </p>
                            <p>
                                <!-- react-text: 190 -->
                                Attribution should be given to
                                <!-- /react-text -->
                                <!-- react-text: 191 -->
                                <!-- /react-text -->
                                <a :href="baseUrl + '/'">PatentsView</a>
                                <!-- react-text: 193 -->
                                <!-- /react-text -->
                                <!-- react-text: 194 -->
                                for use, distribution, or derivative works.
                                <!-- /react-text -->
                            </p>
                        </div>
                        <div class="logo-link">
                            <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank">
                                <img
                                    class="logo-img"
                                    alt="Creative Commons License"
                                    :src="cc_logo"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <span class="overlay block-mouse" v-if="modalIsShow">
            <div class="overlay-shell">
                <div class="pane center-pane">
                    <div class="paneNav">
                        <ul>
                            <li :class="isAboutPane ? 'active': null" @click="isAboutPane = true">
                                <a>About</a>
                            </li>
                            <li :class="!isAboutPane ? 'active': null" @click="isAboutPane = false">
                                <a>Office of Chief Economist</a>
                            </li>
                        </ul>
                        <button class="close" alt="close" @click="modalIsShow = false"></button>
                    </div>
                    <div class="paneCopy" v-if="isAboutPane">
                        <h3>About</h3>
                        <div class="col2">
                            <p>
                                PatentsView is a patent data visualization and analysis platform intended to increase the value, utility, and transparency of US patent data. The initiative is supported by the
                                <a
                                    href="http://www.google.com/url?q=http%3A%2F%2Fwww.uspto.gov%2Fabout-us%2Forganizational-offices%2Foffice-policy-and-international-affairs%2Foffice-chief-economist-6&amp;sa=D&amp;sntz=1&amp;usg=AFQjCNGsYgQiw0dYC1F9VLj69ruvC0is2Q"
                                    target="_blank"
                                >Office of Chief Economist</a> in the US Patent &amp; Trademark Office (USPTO)
                            </p>
                            <p>
                                The PatentsView initiative was established in 2012 and is a collaboration between USPTO, US Deptartment of Agriculture (USDA)
                                <sup
                                    class="footnote-reference"
                                >(1)</sup>, the
                                <a
                                    href="https://www.air.org"
                                    target="_blank"
                                >American Institutes for Research</a>
                                <a></a>, New York University, the
                                <a
                                    href="http://www.funginstitute.berkeley.edu/"
                                    target="_blank"
                                >University of California at Berkeley</a>, Twin Arch Technologies, and
                                <a
                                    href="http://periscopic.com"
                                    target="_blank"
                                >Periscopic</a>.
                            </p>
                            <p>The PatentsView platform is built on a newly developed database that longitudinally links inventors, their organizations, locations, and overall patenting activity. The platform uses data derived from USPTO bulk data files. These data are provided for research purposes and do not constitute the official USPTO record. The data visualization tool, query builder, bulk downloads, and flexible API enable a broad spectrum of users to examine the dynamics of inventor patenting activity over time and space. They also permit users to explore patent technologies, assignees, citation patterns and co-inventor networks.</p>
                            <p>For researchers in particular, PatentsView is intended to encourage the study and understanding of the intellectual property (IP) and innovation system; to serve as a fundamental function of the government in creating “public good” platforms in these data; and to eliminate redundant cleaning, converting and matching of these data by individual researchers, thus freeing up researcher time to do what they do best—study IP, innovation, and technological change.</p>
                            <p>
                                This initiative is directly responsive to President Obama’s open government agenda. In the
                                <a
                                    href="http://www.whitehouse.gov/the_press_office/Transparency_and_Open_Government/"
                                    target="_blank"
                                >Memorandum on Transparency and Open Government</a>, issued on January 21, 2009, the President instructed the Director of the Office of Management and Budget (OMB) to issue an
                                <a
                                    href="http://www.whitehouse.gov/open/documents/open-government-directive"
                                    target="_blank"
                                >Open Government Directive</a>. This directive described the steps that executive agencies should take towards the goal of creating a more open government. Those steps are: 1) publish government information online; 2) improve the quality of government information; 3) create and institutionalize a culture of open government; and 4) create an enabling policy framework for open government.
                            </p>
                            <p>
                                The current PatentsView platform is a being developed for a broad user community and the team welcomes
                                <a
                                    href="mailto:contact@patentsview.org"
                                >feedback</a> on data discrepancies.
                            </p>
                        </div>

                        <div class="footnotes">
                            <sup
                                id="footnote1"
                            >(1) In 2014, USDA&#8217;s Agricultural Research Service successfully piloted a study to demonstrate the feasibility of using PatentsView data to automatically describe the patenting activity of USDA-supported researchers. USDA administrative data from the National Institute of Food and Agriculture, the Agricultural Research Service, and the US Forest Service was linked with PatentsView data.</sup>
                        </div>
                    </div>
                    <div class="paneCopy" v-if="!isAboutPane">
                        <h3>Office of Chief Economist</h3>
                        <div class="col2">
                            <p>With the creation of the USPTO Office of Chief Economist (OCE) in 2008, the USPTO is putting greater emphasis on building an infrastructure that supports economics and statistical research. This research is important to the USPTO not only as an input into evidence-based policymaking, but also as a contribution to knowledge about the IP system and the USPTO’s role within that system.</p>

                            <p>OCE is committed to the following research responsibilities:</p>

                            <ol>
                                <li>Support evidence-based policymaking by bringing new skills and capabilities into the USPTO to analyze ongoing and planned efforts;</li>
                                <li>Support research on important IP questions by partnering with foundations, organizations and academics;</li>
                                <li>Communicate economic thinking about IP both internally at the USPTO and externally to stakeholders, researchers, and the public;</li>
                                <li>Facilitate the use of USPTO data by rationalizing USPTO data for research purposes, by making data easily accessible, and by supporting data matching to other microdata sources.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </span>
    </div>
</template>

<script>
import baseUrl from '../baseUrl'
import uspto_logo from '../img/uspto.png'
import cc_logo from '../img/creative-commons.png'

export default {
    data() {
        return {
            baseUrl: '',
            uspto_logo: '',
            cc_logo: '',
            modalIsShow: false,
            isAboutPane: true,
        }
    },
    mounted() {
        this.baseUrl = baseUrl
        this.uspto_logo = uspto_logo
        this.cc_logo = cc_logo
    }
}
</script>

<style scoped>
.overlay sup {
    line-height: 15px;
}
footer li.about:hover {
    cursor: pointer;
}

footer ul {
    list-style: none;
    line-height: 10px;
}

footer {
    margin: 0;
    padding: 0;
    border: 0;
    font-size: 100%;
    font: inherit;
    vertical-align: baseline;
    padding-bottom: 20px;
}

footer a:hover {
    font-weight: 300;
}

footer.thin-footer {
    color: #4f5f6f;
    font-family: Open Sans, sans-serif;
    font-size: 12px;
}

footer.fat-footer.primary-footer li,
footer.fat-footer h4 {
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

footer.fat-footer .center-shell {
    margin: 0 auto;
    width: 1024px;
}

footer.fat-footer .center-shell:after {
    display: block;
    content: "";
    clear: both;
}

footer.thin-footer {
    z-index: 1000;
    padding: 0 14px;
    box-sizing: border-box;
    background: #4f5f6f;
    color: #fff;
    bottom: 0;
    font-size: 11px;
    font-weight: 400;
    letter-spacing: 0.03em;
    position: fixed;
    width: 100%;
}

footer.thin-footer > div {
    overflow: hidden;
    *zoom: 1;
    margin: 0 auto;
    width: 985px;
}

@media (max-width: 1012px) {
    footer.thin-footer > div {
        width: 758px;
    }
}

footer.thin-footer a,
footer.thin-footer a:visited {
    color: #fff;
}

footer.thin-footer a {
    margin-top: 1px;
}

footer.thin-footer div {
    height: 25px;
    position: relative;
}

footer.thin-footer p {
    padding-top: 6px;
    text-align: center;
}

footer.thin-footer nav {
    left: 0;
    position: absolute;
    top: 0;
    width: 100%;
}

footer.thin-footer li {
    border-right: 1px solid #425161;
    display: block;
    float: left;
}

footer.thin-footer li.feedback,
footer.thin-footer li.glossary {
    float: right;
}

footer.thin-footer li.about,
footer.thin-footer li.glossary {
    border-left: 1px solid #425161;
}

footer.thin-footer a {
    display: block;
    height: 20px;
    padding: 5px 30px 0;
    text-align: center;
    text-decoration: none;
}

footer.thin-footer a:active,
footer.thin-footer a:hover {
    color: #d0dded;
}

footer.fat-footer a {
    color: #d0dded;
}

footer.fat-footer a:hover {
    border-bottom-color: #e17954;
    color: #e7e3b9;
}

footer.fat-footer {
    color: #d0dded;
    font-weight: 300;
}

footer.fat-footer .column {
    float: left;
    box-sizing: border-box;
}

footer.fat-footer h4 {
    font-weight: 300;
    font-size: 10px;
    font-size: 1rem;
    color: #e7e3b9;
}

footer.fat-footer ul {
    padding: 0;
}

footer.fat-footer li {
    margin: 10px 0 0;
    padding: 0 0 8px 12px;
    position: relative;
}

footer.fat-footer.primary-footer .new-credit p,
footer.fat-footer.primary-footer .terms-and-services p {
    line-height: 18px;
    font-size: 11px;
    margin-top: 11px;
    color: #d0dded;
}

footer.fat-footer.primary-footer .new-credit a,
footer.fat-footer.primary-footer .terms-and-services a {
    text-decoration: underline;
}

footer.fat-footer.secondary-footer {
    background: #505f6e;
}

footer.fat-footer.secondary-footer li {
    margin: 5px 0 0;
}

footer.fat-footer.secondary-footer li:after,
footer.fat-footer.secondary-footer li:before {
    display: block;
    content: "";
    position: absolute;
    left: 0;
    border: 3px solid transparent;
}

footer.fat-footer.secondary-footer li:before {
    top: 8px;
    border-top-color: #e7e3b9;
}

footer.fat-footer.secondary-footer li:after {
    top: 2px;
    border-bottom-color: #e7e3b9;
}

footer.fat-footer.secondary-footer .email {
    width: 100%;
    margin-left: 42px;
}

footer.fat-footer.primary-footer {
    background: #3f4b58;
    min-height: 304px;
}

footer.fat-footer.primary-footer a:hover {
    color: #e7e3b9;
}

footer.fat-footer.primary-footer .column {
    padding-top: 26px;
    padding-right: 35px;
}

footer.fat-footer.primary-footer .column.one {
    width: 238px;
    margin-left: 42px;
}

footer.fat-footer.primary-footer .column.two {
    width: 229px;
}

footer.fat-footer.primary-footer .column.three {
    width: 272px;
}

footer.fat-footer.primary-footer .column.four {
    width: 235px;
}

footer.fat-footer.primary-footer .logo-link a {
    text-decoration: none;
    display: block;
}

footer.fat-footer.primary-footer .logo-link .logo-img {
    margin: 15px 0 10px;
}

footer.fat-footer.primary-footer .new-info li:after,
footer.fat-footer.primary-footer .new-info li:before {
    display: block;
    content: "";
    position: absolute;
    left: 0;
    border: 3px solid transparent;
}

footer.fat-footer.primary-footer .new-info li:before {
    top: 6px;
    border-top-color: #e7e3b9;
}

footer.fat-footer.primary-footer .new-info li:after {
    top: 0;
    border-bottom-color: #e7e3b9;
}

footer.fat-footer.primary-footer .copyright {
    margin: 10px 0 0;
}

footer.fat-footer.primary-footer .new-data-sources li {
    list-style: none;
    position: relative;
}

footer.fat-footer.primary-footer .new-data-sources li:after,
footer.fat-footer.primary-footer .new-data-sources li:before {
    display: block;
    content: "";
    position: absolute;
    left: 0;
    border: 3px solid transparent;
}

footer.fat-footer.primary-footer .new-data-sources li:before {
    top: 6px;
    border-top-color: #e7e3b9;
}

footer.fat-footer.primary-footer .new-data-sources li:after {
    top: 0;
    border-bottom-color: #e7e3b9;
}

footer.fat-footer.primary-footer .new-data-sources li svg.icon {
    position: absolute;
    left: 0;
    top: 0;
    font-size: 1.1em;
    fill: #e7e3b9;
}

footer.fat-footer.primary-footer .new-data-sources .update-date {
    font-size: 12px;
    font-size: 1.2rem;
    font-style: italic;
    margin-top: 18px;
}

footer.fat-footer.primary-footer ul {
    margin: 20px 0 0;
}

footer.fat-footer.primary-footer ul a {
    text-decoration: none;
}

footer.fat-footer.primary-footer li {
    font-size: 10px;
    font-size: 1rem;
}

footer.fat-footer.primary-footer li a {
    color: #9cabb9;
}

/* About modal styles */

.overlay h3 {
    font-weight: 500;
    font-style: normal;
}

.overlay p {
    font-weight: 400;
}

.paneNav a {
    transform: translateY(-4px);
    display: inline-block;
}

.paneNav button {
    transform: translateY(-19px);
}

.overlay .pane .paneNav button.close {
    background: transparent;
    border: 0;
    line-height: 14px;
    outline: 0;
    opacity: 0.7;
}
.overlay .pane .paneNav button.close:hover {
    opacity: 1;
}

.overlay .pane .paneNav button.close:after {
    content: "\D7";
    font-weight: 300;
    display: inline-block;
    text-align: center;
    font-size: 10px;
    color: silver;
    background: #4b5e6e;
    width: 14px;
    height: 14px;
    margin-left: 4px;
    border-radius: 7px;
    transition: color 0.2s ease-in-out;
    position: relative;
    top: -1px;
    padding-top: 5px;
    line-height: 0;
    box-sizing: border-box;
}

.overlay a,
.overlay a:visited {
    color: #4f5f6f !important;
    font-weight: 700;
}

.overlay a:hover,
.overlay a:visited:hover {
    color: #e17954;
    font-weight: 700;
}

.overlay .overlay-shell {
    z-index: 1001;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    padding: 0 20px;
    bottom: 0;
    overflow-y: scroll;
}

.overlay.vertical-center {
    text-align: center;
}

.overlay.vertical-center:before {
    content: "";
    height: 100%;
    margin-right: -0.25em;
}

.overlay.vertical-center .pane,
.overlay.vertical-center:before {
    display: inline-block;
    vertical-align: middle;
}

.overlay.block-mouse .overlay-shell {
    background: rgba(0, 0, 0, 0.2);
}

.overlay:not(.block-mouse) .overlay-shell {
    pointer-events: none;
}

.overlay .pane {
    border-radius: 3px;
    border: 1px solid #93a5a5;
    background: #d0dded;
    position: relative;
    top: 0;
    pointer-events: all;
}

.overlay .pane.center-pane {
    text-align: left;
    margin: 96px auto 10px;
    max-width: 982px;
}

.overlay .pane.right-pane {
    width: 753px;
    margin: 125px auto 10px;
    left: 115px;
}

.overlay .pane .paneNav {
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
    border-bottom: 1px solid #445160;
    position: relative;
    height: 35px;
    text-align: right;
}

.overlay .pane .paneNav button.close {
    margin: 8px 7px 0 0;
}

.overlay .pane .paneNav ul {
    margin: 0;
    padding: 11px 0 0 25px;
    float: left;
}

.overlay .pane .paneNav li {
    margin: 0;
    display: inline-block;
    position: relative;
    font-size: 13px;
    font-weight: 600;
    color: #445160;
    cursor: pointer;
}

.overlay .pane .paneNav li:after,
.overlay .pane .paneNav li:before {
    display: block;
    content: "";
    position: absolute;
    left: -10px;
    border: 3px solid transparent;
}

.overlay .pane .paneNav li:before {
    top: 6px;
    border-top-color: #879fd6;
}

.overlay .pane .paneNav li:after {
    top: 0;
    border-bottom-color: #879fd6;
}

.overlay .pane .paneNav li.active:before {
    border-top-color: #e17954;
}

.overlay .pane .paneNav li.active:after {
    border-bottom-color: #e17954;
}

.overlay .pane .paneNav li + li {
    margin-left: 20px;
}

.overlay .pane .col2 {
    -webkit-column-count: 2;
    -ms-column-count: 2;
    -o-column-count: 2;
    column-count: 2;
    -webkit-column-gap: 40px;
    -ms-column-gap: 40px;
    -o-column-gap: 40px;
    column-gap: 40px;
}

.overlay .pane .paneCopy {
    border-bottom-left-radius: 3px;
    border-bottom-right-radius: 3px;
    padding: 25px;
    background: #e5ebf0;
    color: #445160;
}

.overlay .pane .paneCopy ol li {
    font-size: 12px;
}

.overlay .pane .paneCopy.patent-class-type .scroll {
    max-height: 450px;
}

.overlay .pane .paneCopy .scroll {
    max-height: 500px;
    overflow-y: scroll;
}

.overlay .pane .paneCopy .scroll:after {
    display: block;
    content: "";
    height: 15px;
}

.overlay .pane .paneCopy h3 {
    font-size: 16px;
    color: #a75a3e;
    margin-bottom: 13px;
}

.overlay .pane .paneCopy h3:not(:first-child) {
    margin-top: 24px;
}

.overlay .pane .paneCopy h4 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 6px;
}

.overlay .pane .paneCopy h4:not(:first-child) {
    margin-top: 24px;
}

.overlay .pane .paneCopy h4 {
    -webkit-column-break-after: avoid;
    column-break-after: avoid;
}

.overlay .pane .paneCopy em {
    font-style: italic;
    font-weight: 600;
}

.overlay .pane .paneCopy ol,
.overlay .pane .paneCopy ul {
    list-style-type: disc;
}

.overlay .pane .paneCopy ol,
.overlay .pane .paneCopy p,
.overlay .pane .paneCopy ul {
    font-size: 12px;
    line-height: 17px;
}

.overlay .pane .paneCopy p + p {
    margin-top: 17px;
}

.overlay .footnotes:before {
    content: "";
    display: block;
    border-top: 1px solid #ccc;
    margin: 19px 0 13px;
}
</style>