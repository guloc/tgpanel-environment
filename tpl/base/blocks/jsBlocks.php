<?php defined('ROCKET_SCRIPT') OR die(header('Location: /not_found')); ?>

<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/toastify.min.js"></script>
<script src="/assets/js/default-assets/setting.js"></script>
<script src="/assets/js/default-assets/scrool-bar.js"></script>
<script src="<?= latest('/assets/js/default-assets/active.js') ?>"></script>

<? if (page_is('/groups')): ?>
    <script src="/assets/js/quill.min.js"></script>
    <!-- Groups -->
    <script>
        // Active Table setting
        function Table(selector, config=undefined) {
            if (typeof config != 'object') {
                let table = document.querySelector(selector);
                if (typeof table.engine == 'undefined') {
                    console.log(`This table (${selector}) is not configured!`);
                    return false;
                }
                return table.engine;
            }
            for (let key in config) this[key] = config[key];

            this.table = document.querySelector(selector);
            this.table.engine = this;
            this.parent = $(this.table).closest(this.selectors.parent);
            this.paginationParent = this.parent.find(this.selectors.pagination).parent()[0];
            
            this.loadPage = (page, scroll=true) => {
                $(this.table).find(this.selectors.cells).animate({opacity: "0.3"}, 3000);
                this.parent.find(this.selectors.pagination).animate({opacity: "0.3"}, 3000);

                let per_page = this.parent.find(this.selectors.per_page).val(),
                    params = {},
                    get_params = '';
                if (typeof per_page != 'undefined')
                    params.per_page = per_page;
                $.each(this.filters, (i, filter) => {
                    let value = this.parent.find('[name="filter['+filter+']"]').val();
                    if (typeof value != 'undefined' && value != '')
                        params['filter['+filter+']'] = value;
                });
                if (Object.keys(params).length) {
                    params = new URLSearchParams(params);
                    get_params = '?' + params.toString();
                }

                $.getJSON(this.url + page + get_params, (data) => {
                    if (typeof data == 'undefined' || typeof data.result == 'undefined' || data.result == 'error') {
                        $(this.table).find('tbody')[0].innerHTML = this.templates.error;
                        this.setPagination('');
                        return false;
                    } else if (data.result.length == 0) {
                        $(this.table).find('tbody')[0].innerHTML = this.templates.nothing;
                        this.setPagination('');
                        return false;
                    }
                    $(this.table).find('thead')[0].innerHTML = this.templates.header;
                    $(this.table).find('tbody')[0].innerHTML = '';
                    data.result.forEach((item, index, array) => {
                        $(this.table).find('tbody')[0].innerHTML += this.templates.row(item);
                    });
                    if (scroll && data.result.length < 8) {
                        window.scrollTo(0, this.parent[0].offsetTop);
                    }
                    this.setPagination(data.pagination);
                    $('.btn_mute, .btn_ban').click(function() {
                        let id = $(this).attr('data-id'),
                            state = ! $(this).hasClass('btn-danger'),
                            button = $(this),
                            btn_title = '';
                        button.find('i').removeClass('bx-volume-mute bx-user-x').addClass('bx-loader bx-spin');
                        $.ajax({
                            url: button.hasClass('btn_mute')
                                 ? '/groups/mute'
                                 : '/groups/ban',
                            type: 'post',
                            data: {
                                id: id,
                                state: state
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (typeof response.error != 'undefined')
                                    toast(response.error);
                                else {
                                    button.removeClass('btn-danger').removeClass('btn-seconary');
                                    if (state) {
                                        button.addClass('btn-danger');
                                        btn_title = button.hasClass('btn_mute')
                                                  ? '<?= safe(lang('unmute')) ?>'
                                                  : '<?= safe(lang('unban')) ?>';
                                        button.prop('title', btn_title);
                                    } else {
                                        button.addClass('btn-secondary');
                                        btn_title = button.hasClass('btn_mute')
                                                  ? '<?= safe(lang('mute')) ?>'
                                                  : '<?= safe(lang('ban')) ?>';
                                        button.prop('title', btn_title);
                                    }
                                }
                            },
                            error: function() {
                                toast('<?= safe(lang('something_goes_wrong')) ?>');
                            }
                        }).always(() => {
                            button.find('i').removeClass('bx-loader bx-spin')
                            if (button.hasClass('btn_mute'))
                                button.find('i').addClass('bx-volume-mute');
                            else
                                button.find('i').addClass('bx-user-x');
                        });
                    });
                });
            };
            this.setPagination = (data) => {
                this.parent.find(this.selectors.pagination).remove();
                this.paginationParent.innerHTML += data;
                this.table = document.querySelector(selector);
                this.table.engine = this;
                this.parent.find(this.selectors.pagination + ' a').click(function(event) {
                    event.preventDefault();
                    let page = $(this).attr('href').replace('/', ''),
                        table = Table(selector);
                    if (page.length == 0) page = '1' + table.urlSuffix;
                    table.loadPage(page);
                });
            };

            this.loadPage('1' + this.urlSuffix, false);
        }
        function tableConfig(params={}) {
            let config = {};
            for (let key in tablesConfig) {
                config[key] = key in params
                            ? ( typeof tablesConfig[key] == 'object'
                                ? Object.assign({}, tablesConfig[key], params[key])
                                : params[key]
                              )
                            : tablesConfig[key];
            }
            return config;
        }
        const tablesConfig = {
            url: '',
            urlSuffix: '',
            filters: [],
            selectors: {
                parent: '.card-body',
                cells: 'td',
                pagination: '.pagination',
                per_page: '.per_page',
            },
            templates: {},
        };

        // Add
        $('#create_group').click(() => {
            $('#group_editor').modal('show');
        });
        // Delete
        $('a.chat-media').click(function(e) {
            if (e.target.tagName == 'I' || e.target.tagName == 'BUTTON')
                e.preventDefault();
        });
        $('.btn-delete').click(function() {
            let id = $(this).attr('data-id'),
                button = $(this);
            button.find('i').removeClass('bx-trash').addClass('bx-loader bx-spin');
            $.ajax({
                url: '/groups/delete',
                type: 'post',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else {
                        button.closest('a.chat-media').remove();

                        <? if ( ! empty($group)): ?>

                            if (id == <?= $group->id ?>)
                                window.location.href = '/groups';

                        <? endif ?>
                    }
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            }).always(() => {
                $(this).find('i').removeClass('bx-loader bx-spin').addClass('bx-trash');
            });
        });

        // Filter
        function filterGroups() {
            let search = $('#groups_filter_input').val().trim();
            if (search.length == 0) {
                $('a.chat-media').attr('style', '');
            } else {
                $('a.chat-media').each(function (i, item) {
                    let name = $(this).find('h6').text();
                    if (name.toLowerCase().includes(search.toLowerCase()))
                        $(this).attr('style', '');
                    else
                        $(this).attr('style', 'display:none !important');
                });
            }
        }
        $('#groups_filter_input').on('keypress', () => filterGroups());
        $('#groups_filter .btn-search').click(() => filterGroups());

        <? if ( ! empty($group)): ?>

            // Tabs
            $('button.btn-tab').click(function() {
                let tab = $(this).attr('data-tab');
                $('button.btn-tab').removeClass('active');
                $(this).addClass('active');
                $('.tab-card').removeClass('active');
                $(`.tab-card[data-tab="${tab}"]`).addClass('active');

            });

            // Table
            let users = new Table('#users', tableConfig({
                url: '<?= "/groups/users/{$group->id}/" ?>',
                templates: {
                    header: `<tr>
                               <th>#</th>
                               <th></th>
                               <th><?= safe(lang('name'), 'escape') ?></th>
                               <th><?= safe(lang('username'), 'escape') ?></th>
                               <th><?= safe(lang('last_visit'), 'escape') ?></th>
                               <th><?= safe(lang('actions'), 'escape') ?></th>
                             </tr>`,
                    error: '<tr><td colspan="6" class="text-center"><?= safe(lang('error_reload'), 'escape') ?></td></tr>',
                    nothing: '<tr><td colspan="6" class="text-center"><?= safe(lang('nothing_found'), 'escape') ?></td></tr>',
                    row: (item) => {
                        let muted_btn = `<button type="button" class="btn_mute btn btn-sm btn-secondary" title="<?= safe(lang('mute')) ?>" data-id="${item.id}">
                                           <i class="bx bx-sm bx-volume-mute"></i>
                                         </button>`,
                            banned_btn = `<button type="button" class="btn_ban btn btn-sm btn-secondary" title="<?= safe(lang('ban')) ?>" data-id="${item.id}">
                                           <i class="bx bx-sm bx-user-x"></i>
                                         </button>`;
                        if (item.actions.muted)
                            muted_btn = `<button type="button" class="btn_mute btn btn-sm btn-danger" title="<?= safe(lang('unmute')) ?>" data-id="${item.id}">
                                           <i class="bx bx-sm bx-volume-mute"></i>
                                         </button>`;
                        if (item.actions.banned)
                            banned_btn = `<button type="button" class="btn_ban btn btn-sm btn-danger" title="<?= safe(lang('unban')) ?>" data-id="${item.id}">
                                           <i class="bx bx-sm bx-user-x"></i>
                                         </button>`;
                        return `<tr>
                                  <td>${item.id}</td>
                                  <td></td>
                                  <td>${item.name}</td>
                                  <td>${item.username}</td>
                                  <td>${item.last_seen}</td>
                                  <td>${muted_btn} ${banned_btn}</td>
                                </tr>`;
                    },
                },
                filters: ['contact'],
            }));
            $('.per_page').change(() => users.loadPage(1));
            $('#users_filter .btn-search').click(() => users.loadPage(1));
            $('[name="filter[contact]"').on('keypress', function(e) {
                if (e.keyCode == 10 || e.keyCode == 13)
                    users.loadPage(1);
            });

            // Stop words
            function setWordlist(wordlist) {
                $('[name="config[stop_words][list]"]').val(btoa(encodeURIComponent(JSON.stringify(wordlist))));
                $('#word_list').html('');
                $.each(wordlist, (i, item) => {
                    $('#word_list').append(`<span class="badge text-bg-dark">${escapeHtml(item)}<i class="bx bx-x btn-del-stopword"></i></span>`);
                });
                $('.btn-del-stopword').click(function() {
                    let span = $(this).closest('span')
                        word = span.text().trim(),
                        wordlist = JSON.parse(decodeURIComponent(atob($('[name="config[stop_words][list]"]').val())));
                        index = wordlist.indexOf(word);
                    if (index > -1) {
                        wordlist.splice(index, 1);
                    }
                    setWordlist(wordlist);
                    span.remove();
                });
            }
            $('#add_stop_word_btn').click(() => {
                let word = $('#stop_word_input').val().trim(),
                    wordlist = JSON.parse(decodeURIComponent(atob($('[name="config[stop_words][list]"]').val())));
                if (word.length > 0 && ! wordlist.includes(word)) {
                    wordlist.push(word);
                }
                setWordlist(wordlist);
                $('#stop_word_input').val('');
            });
            $('#stop_word_input').on('keypress', function(e) {
                if (e.keyCode == 10 || e.keyCode == 13) {
                    e.preventDefault();
                    $('#add_stop_word_btn').click();
                }
            });
            $('.btn-del-stopword').click(function() {
                let span = $(this).closest('span')
                    word = span.text().trim(),
                    wordlist = JSON.parse(decodeURIComponent(atob($('[name="config[stop_words][list]"]').val())));
                    index = wordlist.indexOf(word);
                if (index > -1) {
                    wordlist.splice(index, 1);
                }
                setWordlist(wordlist);
                span.remove();
            });

            // Editor
            let editor;
            $(function () {
                var icons = Quill.import("ui/icons");
                icons["undo"] = `<svg viewbox="0 0 18 18"><polygon class="ql-fill ql-stroke" points="6 10 4 12 2 10 6 10"></polygon><path class="ql-stroke" d="M8.09,13.91A4.6,4.6,0,0,0,9,14,5,5,0,1,0,4,9"></path></svg>`;
                icons["redo"] = `<svg viewbox="0 0 18 18"><polygon class="ql-fill ql-stroke" points="12 10 14 12 16 10 12 10"></polygon><path class="ql-stroke" d="M9.91,13.91A4.6,4.6,0,0,1,9,14a5,5,0,1,1,5-5"></path></svg>`;
                var options = {
                  // debug: 'info',
                  modules: {
                    toolbar: '#toolbar',
                    toolbar: [
                        ['undo', 'redo'],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['link', 'clean'],
                    ],
                  },
                  theme: 'snow',
                };
                editor = new Quill('#editor', options);
                // on change
                editor.on('text-change', function(delta, source) {
                    $('#editor img').remove();
                });
                // buttons
                document.querySelector(".ql-undo").addEventListener("click", () => {
                    editor.history.undo();
                });
                document.querySelector(".ql-redo").addEventListener("click", () => {
                    editor.history.redo();
                });
                $('#btn_send').click(() => {
                    let content = editor.root.innerHTML;
                    $('#editor_loader').show();
                    $.ajax({
                        url: '/groups/send/<?= $group->id ?>',
                        type: 'post',
                        data: { text: content },
                        dataType: 'json',
                        success: function(response) {
                            if (typeof response.error != 'undefined')
                                toast(response.error);
                            else {
                                toast('<?= safe(lang('sent')) ?>', 'success');
                                editor.root.innerHTML = '';
                            }
                        },
                        error: function() {
                            toast('<?= safe(lang('something_goes_wrong')) ?>');
                        }
                    }).always(() => {
                        $('#editor_loader').hide();
                    });
                });

            });

        <? endif; ?>

    </script>
<? elseif (page_is('/channels')): ?>
    <!-- Channels -->
    <script src="/assets/js/apexcharts.min.js"></script>
    <script>
        // Graph
        const graph_data = [

            <? foreach ($graph as $date => $item): ?>
                
                {
                    date: '<?= $date ?>',
                    join: <?= $item['join'] ?>,
                    left: -<?= $item['left'] ?>
                },

            <? endforeach ?>
            
        ];
        let week_graph = new ApexCharts(document.getElementById('subs-graph'), {
            series: [
                {
                    name: '<?= safe(lang('subscribes')) ?>',
                    data: graph_data.map(row => row.join)
                },
                {
                    name: '<?= safe(lang('unsubscribes')) ?>',
                    data: graph_data.map(row => row.left)
                },
            ],
            chart: {
                height: 200,
                type: 'bar',
                toolbar: { show: !1 },
                offsetX: - 20,
                stacked: !0
            },
            colors: [ '#198754', '#DC3545' ],
            plotOptions: {
                bar: {
                    horizontal: !1,
                    barHeight: '60%',
                    columnWidth: '20%',
                    borderRadius: [ 5 ],
                    borderRadiusApplication: 'end',
                    borderRadiusWhenStacked: 'all'
                }
            },
            stroke: { show: !1 },
            dataLabels: { enabled: !1 },
            legend: { show: !1 },
            grid: { show: !1 },
            xaxis: {
                categories: graph_data.map(row => row.date),
                axisTicks: { show: !1 }
            },
            yaxis: {
                labels: {
                  formatter: function (value) {
                    return value.toPrecision();
                  }
                },
            },
            tooltip: {
                theme: 'dark'
            }
        });
        week_graph.render();

        // Add
        $('#create_channel').click(() => {
            $('#channel_editor').modal('show');
        });
        $('[name="create[platform]"]').change(function() {
            $('[name="create[access_token]"]').prop('requred', 0);
            $('[name="create[access_token]"]').closest('div').hide();
            $('[name="create[access_name]"]').prop('requred', 0);
            $('[name="create[access_name]"]').closest('div').hide();
            if ($(this).val() == 'telegram') {
                $('[name="create[link]"]').attr(
                    'placeholder',
                    '<?= safe(lang('enter_channel_link_tg'), 'escape') ?>'
                );
            } else if ($(this).val() == 'vk') {
                $('[name="create[link]"]').attr(
                    'placeholder',
                    '<?= safe(lang('enter_channel_link_vk'), 'escape') ?>'
                );
                $('[name="create[access_token]"]').attr(
                    'placeholder',
                    '<?= safe(lang('access_key_placeholder_vk'), 'escape') ?>'
                );
                $('[name="create[access_token]"]').prop('requred', 1);
                $('[name="create[access_token]"]').closest('div').show();
            } else if ($(this).val() == 'wordpress') {
                $('[name="create[link]"]').attr(
                    'placeholder',
                    '<?= safe(lang('enter_channel_link_wp'), 'escape') ?>'
                );
                $('[name="create[access_token]"]').attr(
                    'placeholder',
                    '<?= safe(lang('access_key_placeholder_wp'), 'escape') ?>'
                );
                $('[name="create[access_token]"]').prop('requred', 1);
                $('[name="create[access_token]"]').closest('div').show();
                $('[name="create[access_name]"]').prop('requred', 1);
                $('[name="create[access_name]"]').closest('div').show();
            }
        });

        // Delete source
        $('.btn-delete-src').click(function() {
            let id = $(this).attr('data-id'),
                button = $(this);
            button.find('i').removeClass('bx-trash').addClass('bx-loader bx-spin');
            $.ajax({
                url: '/parsing/delete',
                type: 'post',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else {
                        button.closest('li.list-group-item').remove();
                        toast('<?= safe(lang('source_deleted')) ?>', 'success');
                    }
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            }).always(() => {
                $(this).find('i').removeClass('bx-loader bx-spin').addClass('bx-trash');
            });
        });

        // Delete channel
        $('#del_channel_btn').click(function() {
            let id = $(this).attr('data-id'),
                button = $(this),
                btn_html = button.html();
            button.html('<i class="bx bx-loader bx-spin"></i>');
            $.ajax({
                url: '/channels/delete',
                type: 'post',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else
                        window.location.href = '/channels';
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            }).always(() => {
                button.html(btn_html);
            });
        });

        // Disable source
        $('.src-toggle').change(function() {
            let toggle = $(this).prop('checked'),
                id = $(this).attr('data-id');
            $.ajax({
                url: '/parsing/toggle',
                type: 'post',
                data: {
                    id: id,
                    active: toggle
                },
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else {
                        if (toggle)
                            toast('<?= safe(lang('source_activated')) ?>', 'success');
                        else
                            toast('<?= safe(lang('source_deactivated')) ?>', 'success');
                    }
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            });
        });
        
    </script>
<? elseif (page_is('/parsing')): ?>
    <!-- Parsing -->
    <script src="/assets/js/quill.min.js"></script>
    <script>
        // Add
        $('#add_channel').click(() => {
            $('#channel_editor').modal('show');
        });
        $('[name="channel[platform]"]').change(function() {
            if ($(this).val() == 'telegram') {
                $('[name="channel[links]"]').attr(
                    'placeholder',
                    `<?= safe(lang('enter_channel_links') . ' ' . lang('enter_channel_link_tg'), 'escape') ?>`
                );
            } else {
                $('[name="channel[links]"]').attr(
                    'placeholder',
                    `<?= safe(lang('enter_channel_links') . ' ' . lang('enter_channel_link_vk'), 'escape') ?>`
                );
            }
        });
        // Delete
        $('a.chat-media').click(function(e) {
            if (e.target.tagName == 'I' || e.target.tagName == 'BUTTON')
                e.preventDefault();
        });
        $('.btn-delete').click(function() {
            let id = $(this).attr('data-id'),
                button = $(this);
            button.find('i').removeClass('bx-trash').addClass('bx-loader bx-spin');
            $.ajax({
                url: '/parsing/delete',
                type: 'post',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else {
                        button.closest('a.chat-media').remove();

                        <? if ( ! empty($channel)): ?>

                            if (id == <?= $channel->id ?>)
                                window.location.href = '/parsing';

                        <? endif ?>
                    }
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            }).always(() => {
                $(this).find('i').removeClass('bx-loader bx-spin').addClass('bx-trash');
            });
        });
        
        // Stop words
        function setWordlist(wordlist) {
            $('[name="config[stop_words]"]').val(btoa(encodeURIComponent(JSON.stringify(wordlist))));
            $('#word_list').html('');
            $.each(wordlist, (i, item) => {
                $('#word_list').append(`<span class="badge text-bg-dark">${escapeHtml(item)}<i class="bx bx-x btn-del-stopword"></i></span>`);
            });
            $('.btn-del-stopword').click(function() {
                let span = $(this).closest('span')
                    word = span.text().trim(),
                    wordlist = JSON.parse(decodeURIComponent(atob($('[name="config[stop_words]"]').val())));
                    index = wordlist.indexOf(word);
                if (index > -1) {
                    wordlist.splice(index, 1);
                }
                setWordlist(wordlist);
                span.remove();
            });
        }
        $('#add_stop_word_btn').click(() => {
            let word = $('#stop_word_input').val().trim(),
                wordlist = JSON.parse(decodeURIComponent(atob($('[name="config[stop_words]"]').val())));
            if (word.length > 0 && ! wordlist.includes(word)) {
                wordlist.push(word);
            }
            setWordlist(wordlist);
            $('#stop_word_input').val('');
        });
        $('#stop_word_input').on('keypress', function(e) {
            if (e.keyCode == 10 || e.keyCode == 13) {
                e.preventDefault();
                $('#add_stop_word_btn').click();
            }
        });
        $('.btn-del-stopword').click(function() {
            let span = $(this).closest('span')
                word = span.text().trim(),
                wordlist = JSON.parse(decodeURIComponent(atob($('[name="config[stop_words]"]').val())));
                index = wordlist.indexOf(word);
            if (index > -1) {
                wordlist.splice(index, 1);
            }
            setWordlist(wordlist);
            span.remove();
        });

        // Start words
        function setStartWordlist(wordlist) {
            $('[name="config[start_words]"]').val(btoa(encodeURIComponent(JSON.stringify(wordlist))));
            $('#start_word_list').html('');
            $.each(wordlist, (i, item) => {
                $('#start_word_list').append(`<span class="badge text-bg-dark">${escapeHtml(item)}<i class="bx bx-x btn-del-startword"></i></span>`);
            });
            $('.btn-del-startword').click(function() {
                let span = $(this).closest('span')
                    word = span.text().trim(),
                    wordlist = JSON.parse(decodeURIComponent(atob($('[name="config[start_words]"]').val())));
                    index = wordlist.indexOf(word);
                if (index > -1) {
                    wordlist.splice(index, 1);
                }
                setStartWordlist(wordlist);
                span.remove();
            });
        }
        $('#add_start_word_btn').click(() => {
            let word = $('#start_word_input').val().trim(),
                wordlist = JSON.parse(decodeURIComponent(atob($('[name="config[start_words]"]').val())));
            if (word.length > 0 && ! wordlist.includes(word)) {
                wordlist.push(word);
            }
            setStartWordlist(wordlist);
            $('#start_word_input').val('');
        });
        $('#start_word_input').on('keypress', function(e) {
            if (e.keyCode == 10 || e.keyCode == 13) {
                e.preventDefault();
                $('#add_start_word_btn').click();
            }
        });
        $('.btn-del-startword').click(function() {
            let span = $(this).closest('span')
                word = span.text().trim(),
                wordlist = JSON.parse(decodeURIComponent(atob($('[name="config[start_words]"]').val())));
                index = wordlist.indexOf(word);
            if (index > -1) {
                wordlist.splice(index, 1);
            }
            setStartWordlist(wordlist);
            span.remove();
        });


        // Subscript Editor
        let editor;
        $(function () {
            const CodeBlockTag = Quill.import('formats/code-block');
            class PreTag extends CodeBlockTag {}
            PreTag.blotName = 'pre';
            PreTag.tagName = 'pre';
            Quill.register('formats/pre', PreTag);
            const StrikeTag = Quill.import('formats/strike');
            class SpoilerTag extends StrikeTag {}
            SpoilerTag.blotName = 'spoiler';
            SpoilerTag.tagName = 'spoiler';
            Quill.register('formats/spoiler', SpoilerTag);

            var icons = Quill.import("ui/icons");
            icons["undo"] = `<svg viewbox="0 0 18 18"><polygon class="ql-fill ql-stroke" points="6 10 4 12 2 10 6 10"></polygon><path class="ql-stroke" d="M8.09,13.91A4.6,4.6,0,0,0,9,14,5,5,0,1,0,4,9"></path></svg>`;
            icons["redo"] = `<svg viewbox="0 0 18 18"><polygon class="ql-fill ql-stroke" points="12 10 14 12 16 10 12 10"></polygon><path class="ql-stroke" d="M9.91,13.91A4.6,4.6,0,0,1,9,14a5,5,0,1,1,5-5"></path></svg>`;
            var options = {
              // debug: 'info',
              modules: {
                toolbar: '#toolbar',
                toolbar: [
                    ['undo', 'redo'],
                    ['bold', 'italic', 'underline', 'strike', 'blockquote', 'code'],
                    ['link', 'clean'],
                ],
              },
              theme: 'snow',
              formats: [
                  'bold',
                  'italic',
                  'link',
                  'strike',
                  'underline',
                  'blockquote',
                  'code',
                  'pre',
                  'spoiler',
                  'code-block'
              ]
            };
            editor = new Quill('#editor', options);
            // on change
            editor.on('text-change', function(delta, source) {
                $('#editor img').remove();
                $('[name="config[subscript]"]').val(editor.root.innerHTML);
            });
            document.querySelector(".ql-undo").addEventListener("click", () => {
                editor.history.undo();
            });
            document.querySelector(".ql-redo").addEventListener("click", () => {
                editor.history.redo();
            });
        });


        // Replaces
        function setReplaces(replaces) {
            $('[name="config[replaces]"]').val(btoa(encodeURIComponent(JSON.stringify(replaces))));
            $('tr.replace-item').remove();
            $.each(replaces, (i, item) => {
                $('#replaces_list').append(`<tr class="replace-item">
                                              <td class="replace-from">${escapeHtml(item.from)}</td>
                                              <td class="replace-to">${escapeHtml(item.to)}</td>
                                              <td class="text-right">
                                                <i class="del_replace_btn bx bx-x bx-md text-danger cursor-pointer"></i>
                                              </td>
                                            </tr>`);
            });
            $('.del_replace_btn').click(function() {
                let tr = $(this).closest('tr'),
                    from = tr.find('.replace-from').text(),
                    replaces = JSON.parse(decodeURIComponent(atob($('[name="config[replaces]"]').val())));
                $.each(replaces, (i, item) => {
                    if (typeof item == 'undefined')
                        return;
                    if (item.from == from)
                        replaces.splice(i, 1);
                });
                setReplaces(replaces);
                tr.remove();
            });
        }
        $('#add_replace_btn').click(() => {
            let replaces = JSON.parse(decodeURIComponent(atob($('[name="config[replaces]"]').val()))),
                from = $('#replace_from_input').val(),
                to = $('#replace_to_input').val();
            if (from.trim() == '')
                return;
            replaces.push({
                from: from,
                to: to
            });
            setReplaces(replaces);
            $('#replace_from_input').val('');
            $('#replace_to_input').val('');
        });
        $('#replace_from_input, #replace_to_input').on('keypress', function(e) {
            if (e.keyCode == 10 || e.keyCode == 13) {
                e.preventDefault();
                $('#add_replace_btn').click();
            }
        });
        $('.del_replace_btn').click(function() {
            let tr = $(this).closest('tr'),
                from = tr.find('.replace-from').text(),
                replaces = JSON.parse(decodeURIComponent(atob($('[name="config[replaces]"]').val())));
            $.each(replaces, (i, item) => {
                if (typeof item == 'undefined')
                    return;
                if (item.from == from)
                    replaces.splice(i, 1);
            });
            setReplaces(replaces);
            tr.remove();
        });

        // Paraphrase
        $('[name="config[paraphrase_active]"]').change(function() {
            if ($(this).prop('checked'))
                $('#prompt_block').show();
            else
                $('#prompt_block').hide();
        });

        // Moderation
        $('[name="config[autopost_on]"]').change(function() {
            if ($(this).prop('checked'))
                $('#moderation_block').show();
            else
                $('#moderation_block').hide();
        });

    </script>
<? elseif (page_is('/posting')): ?>
    <!-- Posting -->
    <script src="/assets/js/quill.min.js"></script>
    <script src="/assets/js/clipboard.min.js"></script>
    <script src="/assets/js/dropzone.min.js"></script>
    <script src="/assets/js/jquery-ui.min.js"></script>
    <script src="/assets/js/Sortable.min.js"></script>
    <script src="/assets/js/jquery-sortable.js"></script>
    <script src="<?= latest('/assets/js/jquery-ui-timepicker-addon.js') ?>"></script>
    <script>
        let page = 1,
            isFetching = false,
            status_class = {
                draft: 'secondary',
                queued: 'primary',
                posted: 'success',
                moderation: 'warning',
            },
            status_title = {
                draft: '<?= safe(lang('post_draft'), 'escape') ?>',
                queued: '<?= safe(lang('post_queued'), 'escape') ?>',
                posted: '<?= safe(lang('post_posted'), 'escape') ?>',
                moderation: '<?= safe(lang('post_moderation'), 'escape') ?>',
            };

        // Datetimepicker
        $('.datetimepicker').datetimepicker({
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm:ss',
            timezone: '+0300'
        });

        // Posts scroll
        if ($.fn.slimscroll) {
            let listHeight = $('#editor_col .card').prop('scrollHeight');
            if (listHeight == 0)
                listHeight = $(window).height();
            $('#postsList').slimscroll({
                height: listHeight  + 'px',
                size: '4px',
                position: 'right',
                color: '#ebebeb',
                alwaysVisible: false,
                distance: '0px',
                railVisible: false,
                wheelStep: 15
            });
        }

        // Create
        $('#create_post').click(() => {
            $('[name="post[id]"').val('');
            $('#post_html').attr('href', '/posting/content?id=');
            $('[name="post[name]"').val('');
            editor.root.innerHTML = '';
            $('[name="post[channel]"').val('');
            $('[name="post[status]"').val('draft');
            $('[name="post[pub_date]"').val('0000-00-00 00:00:00');
            $('#file_names').val('[]');
            $('#post-files-list').html('');
            $('a.chat-media').removeClass('active');
            if ($('#status_select').val() == '')
                window.history.pushState('', '', '/posting');
            else
                window.history.pushState('', '', '/posting?status=' + $('#status_select').val());
            $('#editor_col').show();
            $('#postsList').slimScroll({ height: $('#editor_col .card').prop('scrollHeight')  + 'px' });
        });

        // Load post
        function loadPost(link_elem) {
            let id = link_elem.attr('data-id'),
                filter_status = $('#status_select').val(),
                files = [],
                query = new URLSearchParams();
            $('#editor_col').show();
            $.ajax({
                url: '/posting/get?id=' + id ,
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else if (typeof response.result == 'undefined')
                        toast('<?= lang('something_goes_wrong') ?>');
                    else {
                        $('[name="post[id]"').val(response.result.id);
                        $('#post_html').attr('href', '/posting/content?id=' + response.result.id);
                        $('[name="post[name]"').val(response.result.name);
                        editor.root.innerHTML = response.result.content;
                        $('[name="post[channel]"').val(response.result.channel_id);
                        $('[name="post[status]"').val(response.result.status);
                        $('[name="post[pub_date]"').val(response.result.pub_date);
                        $('#file_names').val(response.result.files);
                        try {
                            files = JSON.parse(response.result.files);
                        } catch (e) {
                            files = [];
                        }
                        $('#post-files-list').html('');
                        $.each(files, (i, item) => addFile(item) );
                        $('a.chat-media').removeClass('active');
                        $('a.chat-media').each((i, item) => {
                            if ($(item).attr('data-id') == id)
                                $(item).addClass('active');
                        });
                        query.append('id', id);
                        if (filter_status != '')
                            query.append('status', filter_status);
                        window.history.pushState('', '', '/posting?' + query.toString());
                        $('#postsList').slimScroll({ height: $('#editor_col .card').prop('scrollHeight')  + 'px' });
                    }
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            });
        }
        $('a.chat-media').click(function(e) {
            e.preventDefault();
            if (e.target.tagName != 'I' && e.target.tagName != 'BUTTON') {
                loadPost($(this));
            }
        });

        // Load page
        function loadPage() {
            let query = new URLSearchParams(),
                filter_status = $('#status_select').val(),
                loader = `<div id="posts_loader" class="chat-media d-flex align-items-center">
                            <div class="chat-media-body d-flex justify-content-center align-items-center p-4">
                              <i class="bx bx-lg bx-loader bx-spin"></i>
                            </div>
                          </div>`;
            $('#postsList').append(loader);
            query.append('page', page);
            if (filter_status != '')
                query.append('status', filter_status);
            $.ajax({
                url: '/posting/posts?' + query.toString(),
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        console.log('Error: ' + response.error);
                    else if (typeof response.result != 'object') {
                        console.log('Error: Incorrect response');
                    }
                    else {
                        response.result.forEach((item, index, array) => {
                            let active = $('[name="post[id]"').val() == item.id
                                       ? 'active'
                                       : '',
                                channel_p = item.channel_name == ''
                                          ? ''
                                          : '<p class="channel_name">' + item.channel_name + '</p>',
                                post_elem = `<a href="/posting?id=${item.id}"
                                                class="chat-media d-flex align-items-center ${active}"
                                                data-status="${item.status}"
                                                data-id="${item.id}">
                                              <div class="chat-media-body d-flex justify-content-between align-items-center">
                                                  <div class="chat-user-info">
                                                    <h6>${escapeHtml(item.name)}</h6>
                                                    <p class="pub_date">${item.pub_date ?? ''}</p>
                                                    ${channel_p}
                                                    <span class="badge text-bg-${status_class[item.status]}">
                                                      ${status_title[item.status]}
                                                    </span>
                                                  </div>
                                                  <button class="btn-delete btn btn-danger btn-sm p-0"
                                                          title="<?= safe(lang('delete'), 'escape') ?>"
                                                          data-id="${item.id}" >
                                                    <i class="bx bx-trash bx-sm"></i>
                                                  </button>
                                              </div>
                                            </a>`,
                                new_post_elem;
                            $('#postsList').append(post_elem);
                            new_post_elem = $('#postsList a.chat-media:last-child');
                            new_post_elem.find('.btn-delete').click(function() {
                                deletePost($(this));
                            });
                            new_post_elem.click(function(e) {
                                e.preventDefault();
                                if (e.target.tagName != 'I' && e.target.tagName != 'BUTTON') {
                                    loadPost($(this));
                                }
                            });
                        });
                        page = response.result.length == 0
                             ? 0
                             : page + 1;
                    }
                },
                error: function() {
                    console.log('Connection error');
                }
            }).always(() => {
                isFetching = false;
                $('#posts_loader').remove();
            });
        }
        $(function () { loadPage(); });
        $('#postsList').on('scroll', (e) => {
            let list = $('#postsList')[0];
            if ( ! isFetching && page != 0 && list.scrollTop + list.offsetHeight >= list.scrollHeight) {
                isFetching = true;
                loadPage();
            }
        })

        // Filter
        $('#status_select').change(function() {
            let status = $(this).val(),
                post_id = $('[name="post[id]"]').val(),
                query = new URLSearchParams();
            $('#postsList a.chat-media').remove();
            page = 1;
            loadPage();

            if (post_id != '')
                query.append('id', post_id);
            if (status != '')
                query.append('status', status);

            if (query.size == 0)
                window.history.pushState('', '', '/posting');
            else
                window.history.pushState('', '', '/posting?' + query.toString());
        });

        // Save
        $('#post_save, #pub_now').click(function() {
            let button = $(this),
                btn_html = button.html();
                post = {
                    id: $('[name="post[id]"').val(),
                    name: $('[name="post[name]"').val(),
                    content: editor.root.innerHTML,
                    status: $('[name="post[status]"').val(),
                    pub_date: $('[name="post[pub_date]"').val(),
                    channel_id: $('[name="post[channel]"').val(),
                    files: $('#file_names').val(),
                };
            if (button.attr('id') == 'pub_now')
                post.pub_now = true;
            button.html('<i class="bx bx-loader bx-spin"></i>');
            $.ajax({
                url: '/posting/edit',
                type: 'post',
                data: { post: post },
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else if (typeof response.result == 'undefined' || response.result != 'ok')
                        toast('<?= safe(lang('something_goes_wrong')) ?>');
                    else {
                        toast('<?= safe(lang('saved')) ?>', 'success');
                        $('[name="post[name]"]').val(response.name);
                        if (post.pub_now == true && typeof response.pub_date != 'undefined') {
                            $('[name="post[pub_date]"').val(response.pub_date);
                            $('[name="post[status]').val('queued');
                            post.pub_date = response.pub_date;
                            post.status = 'queued';
                        }
                        if (post.id == '') {
                            let channel_p = response.channel_name == ''
                                          ? ''
                                          : `<p class="channel_name">${escapeHtml(response.channel_name)}</p>`,
                                badge = '',
                                post_item = '',
                                query = new URLSearchParams();
                            switch (response.status) {
                                case 'queued':
                                    badge = `<span class="badge text-bg-primary">
                                               <?= safe(lang('post_queued')) ?>
                                             </span>`;
                                    break;
                                case 'draft':
                                    badge = `<span class="badge text-bg-secondary">
                                              <?= safe(lang('post_draft')) ?>
                                            </span>`;
                                    break;
                                case 'moderation':
                                    badge = `<span class="badge text-bg-warning">
                                              <?= safe(lang('post_moderation')) ?>
                                            </span>`;
                                    break;
                                case 'posted':
                                    badge = `<span class="badge text-bg-success">
                                              <?= safe(lang('post_posted')) ?>
                                            </span>`;
                                    break;
                            }
                            $('a.chat-media').removeClass('active');
                            post_item = $(`<a href="/posting?id=${response.id}" class="chat-media d-flex align-items-center active" data-status="${post.status}" data-id="${response.id}">
                                            <div class="chat-media-body d-flex justify-content-between align-items-center">
                                              <div class="chat-user-info">
                                                <h6>${escapeHtml(response.name)}</h6>
                                                <p class="pub_date">${response.pub_date ?? post.pub_date}</p>
                                                ${channel_p}
                                                ${badge}
                                              </div>
                                              <button class="btn-delete btn btn-danger btn-sm p-0"
                                                      title="<?= safe(lang('delete')) ?>"
                                                      data-id="${response.id}" >
                                                <i class="bx bx-trash bx-sm"></i>
                                              </button>
                                            </div>
                                          </a>`);
                            $('#postsList').append(post_item);
                            post_item.find('.btn-delete').click(function() {
                                deletePost($(this));
                            });
                            post_item.click(function(e) {
                                e.preventDefault();
                                if (e.target.tagName != 'I' && e.target.tagName != 'BUTTON') {
                                    loadPost($(this));
                                }
                            });
                            query.append('id', response.id);
                            if ($('#status_select').val() != '')
                                query.append('status', $('#status_select').val());
                            window.history.pushState('', '', '/posting?' + query.toString());
                            $('[name="post[id]"]').val(response.id);
                            $('#post_html').attr('href', '/posting/content?id=' + response.result.id);
                        } else {
                            $('.chat-media').each((i, item) => {
                                if ($(item).attr('data-id') == post.id) {
                                    $(item).find('h6').text(post.name);
                                    $(item).find('p.pub_date').text(post.pub_date);
                                    $(item).find('p.channel_name').text($('[name="post[channel]"]>option:selected').text().trim());
                                    let badge = $(item).find('.badge');
                                    badge.removeClass('text-bg-primary')
                                         .removeClass('text-bg-secondary')
                                         .removeClass('text-bg-success')
                                         .removeClass('text-bg-warning');
                                    switch (post.status) {
                                        case 'queued':
                                            badge.addClass('text-bg-primary');
                                            badge.text('<?= safe(lang('post_queued')) ?>');
                                            break;
                                        case 'draft':
                                            badge.addClass('text-bg-secondary');
                                            badge.text('<?= safe(lang('post_draft')) ?>');
                                            break;
                                        case 'moderation':
                                            badge.addClass('text-bg-warning');
                                            badge.text('<?= safe(lang('post_moderation')) ?>');
                                            break;
                                        case 'posted':
                                            badge.addClass('text-bg-success');
                                            badge.text('<?= safe(lang('post_posted')) ?>');
                                            break;
                                    }
                                }
                            });
                        }
                    }
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            }).always(() => {
                button.html(btn_html);
            });
        });

        // Delete
        function deletePost(button) {
            let id = button.attr('data-id');
            button.find('i').removeClass('bx-trash').addClass('bx-loader bx-spin');
            $.ajax({
                url: '/posting/delete',
                type: 'post',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else {
                        button.closest('a.chat-media').remove();
                        if (id == $('[name="post[id]"]').val()) {
                            if ($('#status_select').val() != '')
                                window.history.pushState('', '', '/posting?status=' + $('#status_select').val());
                            else
                                window.history.pushState('', '', '/posting');
                            $('#editor_col').hide();
                        }
                    }
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            }).always(() => {
                button.find('i').removeClass('bx-loader bx-spin').addClass('bx-trash');
            });
        }
        $('.btn-delete').click(function() {
            deletePost($(this));
        });

        // Editor
        let editor;
        $(function () {
            const CodeBlockTag = Quill.import('formats/code-block');
            class PreTag extends CodeBlockTag {}
            PreTag.blotName = 'pre';
            PreTag.tagName = 'pre';
            Quill.register('formats/pre', PreTag);
            const StrikeTag = Quill.import('formats/strike');
            class SpoilerTag extends StrikeTag {}
            SpoilerTag.blotName = 'spoiler';
            SpoilerTag.tagName = 'spoiler';
            Quill.register('formats/spoiler', SpoilerTag);

            var icons = Quill.import("ui/icons");
            icons["undo"] = `<svg viewbox="0 0 18 18"><polygon class="ql-fill ql-stroke" points="6 10 4 12 2 10 6 10"></polygon><path class="ql-stroke" d="M8.09,13.91A4.6,4.6,0,0,0,9,14,5,5,0,1,0,4,9"></path></svg>`;
            icons["redo"] = `<svg viewbox="0 0 18 18"><polygon class="ql-fill ql-stroke" points="12 10 14 12 16 10 12 10"></polygon><path class="ql-stroke" d="M9.91,13.91A4.6,4.6,0,0,1,9,14a5,5,0,1,1,5-5"></path></svg>`;
            var options = {
              // debug: 'info',
              modules: {
                toolbar: '#toolbar',
                toolbar: [
                    ['undo', 'redo'],
                    ['bold', 'italic', 'underline', 'strike', 'blockquote', 'code'],
                    ['link', 'clean'],
                ],
              },
              theme: 'snow',
              formats: [
                  'bold',
                  'italic',
                  'link',
                  'strike',
                  'underline',
                  'blockquote',
                  'code',
                  'pre',
                  'spoiler',
                  'code-block'
              ]
            };
            editor = new Quill('#editor', options);
            $('#words_count').text(editor.getText().length - 1);
            // on change
            editor.on('text-change', function(delta, source) {
                $('#editor img').remove();
                let content = editor.root.innerHTML,
                    count = editor.getText().length - 1;
                $('#words_count').text(count);
            });
            document.querySelector(".ql-undo").addEventListener("click", () => {
                editor.history.undo();
            });
            document.querySelector(".ql-redo").addEventListener("click", () => {
                editor.history.redo();
            });
        });

        // Upload
        $('#upload_button').click(() => {
            $('.main-buttons').attr('style', 'display:none!important');
            $('.upload-zone').show();
        });
        $('#upload_back').click(() => {
            $('.upload-zone').hide();
            $('.main-buttons').show();
        });
        Dropzone.options.myDropzone = {
            paramName: "file",
            maxFilesize: <?= $max_filesize / 1000000 ?>, // MB
            uploadMultiple: true,
            success: function (file) {
                let response,
                    files;
                try {
                    response = JSON.parse(file.xhr.response);
                    if (typeof response.error != 'undefined') {
                        toast(response.error);
                        this.removeAllFiles();
                    } else if (typeof response.result == 'undefined') {
                        toast('<?= safe(lang('something_goes_wrong')) ?>');
                        this.removeAllFiles();
                    } else {
                        try {
                            files = JSON.parse($('#file_names').val());
                        } catch (e) {
                            files = [];
                        }
                        files = removeDuplicates(files.concat(response.result));
                        $('#file_names').val(JSON.stringify(files));
                        $('#post-files-list').html('');
                        $.each(files, (i, item) => addFile(item) );
                    }
                    Dropzone.forElement('#my-dropzone').removeFile(file);
                } catch (e) {
                    console.log(e);
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                    this.removeAllFiles();
                }
            },
            error: function(file, message) {
                console.log(message);
                if (message.indexOf('You can not upload any more files') != -1) {
                    this.removeAllFiles();
                    this.addFile(file);
                } else {
                    if (message.indexOf('File is too big') != -1)
                        message = '<?= safe(lang('file_too_big')) ?>';
                    toast(message);
                    this.removeAllFiles();
                }
            }
        };
        function mediaElement(link) {
            let types = {
                  <? foreach ($media_extensions as $type => $extensions): ?>
                    <?= $type ?>: [
                        <? foreach ($extensions as $ext): ?>
                            '<?= $ext ?>',
                        <? endforeach ?>
                    ],
                  <? endforeach ?>
                },
                ext = link.toLowerCase().match(/\.(\w+)$/)[1],
                elem = '';
            link = link.toLowerCase();
            $.each(types, (type, extensions) => {
                if (extensions.includes(ext)) {
                    if (type == 'photo')
                        elem = `<img src="${link}">`;
                    else if  (type == 'video')
                        elem = `<video src="${link}">`;
                    else if  (type == 'audio')
                        elem = `<audio src="${link}">`;
                }
            });
            return elem;
        }
        function addFile(file_name) {
            let elem = $(`<div class="post-file">
                            <i class='bx bx-x bx-sm px-2 cursor-pointer btn-del-file' data-file="${file_name}"></i>
                            <a href="/assets/upload/<?= $userinfo->id ?>/${file_name}" target="_blank">
                              ${file_name}
                            </a>
                            <div class="media-container d-flex justify-content-center">
                              ${mediaElement('/assets/upload/<?= $userinfo->id ?>/'+file_name)}
                            </div>
                          </div>`);
            $('#post-files-list').append(elem);
            elem.find('i').click(function() {
                delFile($(this).attr('data-file'));
            });
        }
        function delFile(file_name) {
            let files = [],
                new_files = [];
            try {
                files = JSON.parse($('#file_names').val());
            } catch (e) {
                files = [];
            }
            $('#post-files-list').html('');
            $.each(files, (i, item) => {
                if (item != file_name) {
                    elem = `<p class="post-file">
                              <i class='bx bx-x bx-sm px-2 cursor-pointer btn-del-file' data-file="${item}"></i>
                              <a href="/assets/upload/<?= $userinfo->id ?>/${item}" target="_blank">
                                ${item}
                              </a>
                            </p>`;
                    new_files.push(item);
                    addFile(item);
                }
            });
            $('#file_names').val(JSON.stringify(new_files));
        }
        $('.btn-del-file').click(function() {
            delFile($(this).attr('data-file'));
        });

        // Sortable
        $('#post-files-list').sortable({
            animation: 150,
            onEnd: () => {
                let files = [];
                $('.btn-del-file').each((i, item) => {
                    files.push($(item).attr('data-file'));
                });
                $('#file_names').val(JSON.stringify(files));
            }
        });

        // Clear all
        $('#clear_all_btn').click(function () {
            let button = $(this),
                btn_html = button.html(),
                filter_status = $('#status_select').val();
            button.html('<i class="bx bx-loader bx-spin"></i>');

            $.ajax({
                url: '/posting/clear_all',
                type: 'post',
                data: { status: filter_status },
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else {
                        window.location = window.location;
                    }
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            }).always(() => {
                button.html(btn_html);
            });

        });
            
    </script>
<? elseif (page_is('/users')): ?>
    <!-- Users -->
    <script>
        // Delete user
        $('.delete_user').click(function() {
            let user_id = $(this).attr('data-id'),
                button = $(this);
            $(this).html('<i class="bx bx-loader bx-spin" aria-hidden="true"></i>');
            $.ajax({
                url: '/users/delete',
                type: 'post',
                data: { user_id: user_id },
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else
                        window.location.href = window.location.href;
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            }).always(() => {
                button.html('<?= safe(lang('delete')) ?>');
            });
        });

        // Change password
        $('.change_pass').click(function() {
            let user_id = $(this).attr('data-id');
            $('[name="update_user[id]"]').val(user_id);
            $('#user_editor').modal('show');
        });
    </script>
<? elseif (page_is('/settings')): ?>
    <!-- Settings -->
    <script>
        // LLM
        $('#llm_model').change(function() {
            let option = $(this).find('option:selected'),
                provider = option.attr('data-provider');
            $(this).attr('name', `settings[${provider}_ai_model]`);
            $('[name="settings[llm_provider]"]').val(provider);
        });
        $('[name="new_model[code]"]').change(function() {
            if ($('[name="new_model[name]"]').val() == '')
                $('[name="new_model[name]"]').val($(this).val())
        });

        // Bot users
        function setUserslist(userlist) {
            $('[name="settings[bot_users]"]').val(btoa(encodeURIComponent(JSON.stringify(userlist))));
            $('#users_list').html('');
            $.each(userlist, (i, item) => {
                let title = i;
                if (item != '')
                    title += ` (${item})`;
                $('#users_list').append(`<span class="badge text-bg-dark">${escapeHtml(title)}<i class="bx bx-x btn-del-bot_user"></i></span>`);
            });
            $('.btn-del-bot_user').click(function() {
                let span = $(this).closest('span')
                    user = span.text().trim().split(' ')[0],
                    userlist = JSON.parse(decodeURIComponent(atob($('[name="settings[bot_users]"]').val())).replace(/\+/g, ' '));
                delete(userlist[user]);
                setUserslist(userlist);
                span.remove();
            });
        }
        $('#add_bot_user_btn').click(() => {
            let user = $('#bot_user_input').val().trim(),
                userlist_str = decodeURIComponent(atob($('[name="settings[bot_users]"]').val()))
                                .replace(/\+/g, ' '),
                userlist = userlist_str == '[]'
                         ? {}
                         : JSON.parse(userlist_str);
            if (user.length > 0)
                userlist[user] = '';
            setUserslist(userlist);
            $('#bot_user_input').val('');
        });
        $('#bot_user_input').on('keypress', function(e) {
            if (e.keyCode == 10 || e.keyCode == 13) {
                e.preventDefault();
                $('#add_bot_user_btn').click();
            }
        });
        $('.btn-del-bot_user').click(function() {
            let span = $(this).closest('span')
                user = span.text().trim().split(' ')[0],
                userlist = JSON.parse(decodeURIComponent(atob($('[name="settings[bot_users]"]').val())).replace(/\+/g, ' '));
            delete(userlist[user]);
            setUserslist(userlist);
            span.remove();
        });

        // Restart unsafe parsing
        $('#restart_parsing_btn').click(function() {
            let button = $(this),
                btn_html = button.html();
            button.html('<i class="bx bx-loader bx-spin"></i>');
            $.ajax({
                url: '/settings/restart_parsing',
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    if (typeof response.error != 'undefined')
                        toast(response.error);
                    else {
                        toast('<?= safe(lang('parsing_restarted')) ?>', 'success');
                    }
                },
                error: function() {
                    toast('<?= safe(lang('something_goes_wrong')) ?>');
                }
            }).always(() => {
                button.html(btn_html);
            });
        });

        // Logout
        $('#tg_logout_btn').click(() => {
            $('#tg_logout_form').submit();
        });

    </script>
<? elseif (page_is('/logs')): ?>
    <!-- Logs -->
    <script>
        
        function viewerSize() {
            if ($.fn.slimscroll) {
                $('#viewer').slimscroll({
                    height: ($(window).height() - 145) + 'px',
                    size: '4px',
                    position: 'right',
                    color: '#ebebeb',
                    alwaysVisible: false,
                    distance: '0px',
                    railVisible: false,
                    wheelStep: 15
                });
            }
        }

        $(function () {
            viewerSize();
            $(window).on('resize', function(){
                viewerSize();
            });
        })
    </script>
<? endif; ?>

<!-- Helpers -->
<script>
    // Cookie edit
    function createCookie(name, value, days=365) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (1000 * days * 24 * 3600));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    // Sanitize text
    function sanitizeText(text) {
        return $('<div>').text(text).html()
    }

    // Escape HTML
    function escapeHtml(string) {
        let entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;',
            '`': '&#x60;',
            '=': '&#x3D;'
        };
        return String(string).replace(/[&<>"'`=\/]/g, function (s) {
                return entityMap[s];
        });
    }

    // Toast
    function toast(text, type='error') {
        let colors = {
            'error': '#E24D46',
            'success': '#2CCC70'
        };
        Toastify({
          text: text,
          duration: 5000,
          close: true,
          gravity: 'top',
          position: 'right',
          stopOnFocus: true,
          style: {
            background: colors[type],
          },
        }).showToast();
    }

    // Remove duplicates
    function removeDuplicates(arr) {
        return arr.filter((item,
            index) => arr.indexOf(item) === index);
    }

</script>