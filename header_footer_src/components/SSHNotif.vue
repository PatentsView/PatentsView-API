<template>
    <div class="banner" :class="closeBanner ? 'closed' : null">
        <div class="button">
            <button @click="closeBanner = true">Close</button>
            <p>
                (will close in
                <span>{{count}}</span> seconds...)
            </p>
        </div>
        <div class="text">
            <p>
                <strong>PatentsView website is now SSL enabled.</strong>
            </p>
            <p>
                The community site has also moved from
                <code>www.patentsview.org/community</code> to
                <code>https://community.patentsview.org</code>.
            </p>
            <p>
                See the community
                <a
                    href="https://community.patentsview.org/forum/7/topic/150"
                >forum post</a> for more details.
            </p>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            count: 20,
            closeBanner: false
        }
    },
    mounted() {
        const countDown = setInterval(() => {
            if (this.count > 0) {
                this.count--
            } else {
                this.closeBanner = true
                window.clearInterval(countDown)
            }
        }, 1000)
    }
}
</script>

<style scoped>
div {
    display: inline-block;
}

.banner {
    position: fixed;
    top: 0;
    background-color: rgb(253, 224, 115, 0.9);
    width: 100%;
    padding: 10px;
    z-index: 999;
    min-height: 50px;
}

.banner a {
    text-decoration: underline;
}

.banner p {
    margin: 0;
}

.text {
    padding-left: 300px;
}

.button {
    position: absolute;
    padding: 10px 0 0 60px;
    text-align: center;
}

.button strong {
    font-size: 20px;
}

.button strong:hover {
    cursor: pointer;
}

.button button {
    border-radius: 5px;
    border: none;
    padding: 5px 15px;
    box-shadow: grey 1px 1px 20px -5px;
    margin-bottom: 5px;
}

.button span {
    color: orange;
    width: 20px;
    display: inline-block;
}

.banner.closed {
    display: none;
}
</style>