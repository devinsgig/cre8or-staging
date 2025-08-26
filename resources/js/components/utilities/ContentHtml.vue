<template>
    <div v-if="content" ref="content" class="whitespace-pre-wrap break-word">
        <div v-if="readMore" class="readMore" v-html="shortContentHtml"></div>
        <div v-else v-html="contentHtml" class="readLess"></div>
    </div>
</template>

<script>
import Constant from '@/utility/constant'
import {uuidv4} from '@/utility/index'

export default {
    props: {
		content: {
			type: String,
			default: ''
		},
		mentions: {
			type: Array,
			default: null
		},
        limit: {
			type: Number,
			default: null
		},
        contentKey: {
            type: Number,
			default: null
        },
        showReadLess: {
            type: Boolean,
            default: true
        }
	},
    data() {
        return {
            readMore: false,
            contentHtml : '',
            shortContentHtml: '',
            limitCharacter: 0,
            maxLimitCharacter: 500
        }
    },
    mounted () {
        this.updateContent()
    },
    updated () {
        this.bindEventHtml()
        this.addReadMoreButton()
    },
    watch: {
        content() {
            this.updateContent()
        },
        contentKey(){ // Fix show more bug when story same content
            this.updateContent()
        }
    },
    methods: {
        addReadMoreButton() {
            const moreElement = this.$refs.content?.querySelector('.readMore');
            if (moreElement) {
                if (moreElement.querySelector('.hasMore') == null) {
                    const more = document.createElement('a');
                    more.innerHTML = ' ' + this.$t('See more');
                    more.setAttribute('href', 'javascript:void(0)');
                    more.classList.add('hasMore', 'inline-block');
                    more.addEventListener('click', (event) => {
                        this.readMore = false;
                        event.preventDefault();
                        this.$emit('click_read_more');
                    });
                    moreElement.appendChild(more);
                }

            }

            if(this.showReadLess){
                const lessElement = this.$refs.content?.querySelector('.readLess');
                if(lessElement){
                    if (lessElement.querySelector('.hasLess') == null && (this.contentHtml.length > this.limitCharacter)) {
                        const less = document.createElement('a');
                        less.innerHTML = ' ' + this.$t('See less');
                        less.setAttribute('href', 'javascript:void(0)');
                        less.classList.add('hasLess', 'inline-block');
                        less.addEventListener('click', (event) => {
                            this.readMore = true;
                            event.preventDefault();
                            this.$emit('click_read_less');
                        });
                        lessElement.appendChild(less);
                    }
                }
            }
        },
        renderFullContent(){
            var result = this.content
            var userMentions = this.mentions
            var self = this
            this.limitCharacter = this.limit || Constant.LIMIT_CHARACTER;

            var lineCount = (result.match(/\n/g) || []).length;
            if (lineCount > 10) {
                var lineLimit = lineCount * 35
                if (lineLimit > 350) {
                    lineLimit = 350
                }
                this.limitCharacter -= lineLimit;
            }
            if (this.limitCharacter < 100)  {
                this.limitCharacter = 50
            }

            //link 
            var tmp_links = [];
            if (result) {
                var links = result.match(Constant.LINK_REGEX)
                if (links && links.length > 0)  {
                    links.map((link, key) => {
                        key = uuidv4()
                        var link_result = '<a class="break-word external-link" target="_blank" href="' + link + '">' + link + '</a>'
                        tmp_links[key] = link.replace(link, link_result)
                        result = result.replace(link, key)
                        self.limitCharacter += link_result.length - link.length;
                        self.maxLimitCharacter += link_result.length - link.length;
                    });                    
                }            
            }

            //mention
            var tmp_mentions = [];
            var mentions = result.match(Constant.MENTION_REGEX);
            if (mentions && mentions.length > 0) {                
                mentions.map((mention ,key) => {
                    var user = window._.find(userMentions, {user_name: mention.replace('@', '')});
                    if (user ) {
                        var profileUrl = self.$router.resolve({
                            name: 'profile',
                            params: {user_name: mention.replace('@', '')}                       
                        })
                        key = uuidv4()
                        var mention_result = '<a class="inline-block internal-link break-word" data-internal_href="'+profileUrl.fullPath+'" href="' + profileUrl.href + '">' + user.name + '</a>'
                        tmp_mentions[key] = mention.replace(mention, mention_result)
                        result = result.replace(mention, key)
                        self.limitCharacter += mention_result.length - mention.length
                        self.maxLimitCharacter += mention_result.length - mention.length
                    }
                });
            }

            //hashtag
            var tmp_hashtags = [];
            if (result) {
                var hashtags = result.match(Constant.HASHTAG_REGEX);
                if (hashtags && hashtags.length > 0) {
                    hashtags.map((hashtag ,key) => {
                        var hashtagUrl = self.$router.resolve({
                            name: 'search',
                            params: {'search_type': 'hashtag', 'type' : 'post'},
                            query: {
                                q: hashtag.replace('#', '')
                            }
                        })
                        key = uuidv4()
                        var hashtag_result = '<a class="inline-block internal-link" data-internal_href="'+hashtagUrl.fullPath+'" href="' + hashtagUrl.href + '">' + hashtag + '</a>';
                        tmp_hashtags[key] = hashtag.replace(hashtag, hashtag_result)
                        result = result.replace(hashtag, key)
                        self.limitCharacter += hashtag_result.length - hashtag.length
                        self.maxLimitCharacter += hashtag_result.length - hashtag.length
                    });                   
                }
            }
            
            window._.forIn(tmp_links, function(tmp_link, key) {
                result = result.replace(key, tmp_link)
                self.limitCharacter += tmp_link.length
            });
            window._.forIn(tmp_hashtags, function(tmp_hashtag, key) {
                result = result.replace(key, tmp_hashtag)
                self.limitCharacter += tmp_hashtag.length
            });
            window._.forIn(tmp_mentions, function(tmp_hashtag, key) {
                result = result.replace(key, tmp_hashtag)
            });
            if (this.limitCharacter > this.maxLimitCharacter)  {
                this.limitCharacter = this.maxLimitCharacter
            }
            return result
        },
        renderShortContent(){
            return this.contentHtml.length > this.limitCharacter
                ? this.contentHtml.slice(0, this.limitCharacter).trim() + ' ... '
                : this.contentHtml
        },
        bindEventHtml() {
            var links = this.$refs.content?.querySelectorAll('a') || []
            if(links.length){
                for (let i = 0; i < links.length; i++) {
                    links[i].addEventListener('click', (event) => {
                        var target = event.target
                        var link = target.getAttribute('data-internal_href')
                        if (target.classList.contains('external-link')) {
                            return
                        } else {
                            this.$router.push(link)
                        }
                        event.preventDefault()
                    })
                }
            }
        },
        updateContent(){
            this.contentHtml = this.renderFullContent(),
            this.shortContentHtml = this.renderShortContent(),
            this.readMore = this.contentHtml.length > this.limitCharacter
        }
    },
    emits: ['click_read_more', 'click_read_less']
}
</script>