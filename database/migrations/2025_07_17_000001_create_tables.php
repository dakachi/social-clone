<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('id_secure', 32)->nullable();
            $table->integer('role')->nullable();
            $table->string('pid', 20)->nullable();
            $table->string('login_type', 20)->nullable();
            $table->string('fullname')->nullable();
            $table->string('username')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password', 100)->nullable();
            $table->string('avatar')->nullable();
            $table->integer('plan_id')->nullable();
            $table->bigInteger('expiration_date')->nullable();
            $table->string('timezone', 50)->nullable();
            $table->string('language', 10)->nullable();
            $table->mediumText('data')->nullable();
            $table->string('secret_key', 50)->nullable();
            $table->integer('last_login')->nullable();
            $table->integer('status')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('addons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('source')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('module_name', 50)->nullable();
            $table->string('purchase_code', 191)->nullable();
            $table->integer('is_main')->nullable();
            $table->string('version', 50)->nullable();
            $table->string('install_path', 255)->nullable();
            $table->string('relative_path', 255)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
            $table->index('purchase_code');
            $table->index('product_id');
        });

        Schema::create('affiliate', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32);
            $table->integer('affiliate_uid');
            $table->integer('payment_id')->nullable();
            $table->float('amount');
            $table->float('commission_rate');
            $table->float('commission');
            $table->integer('status');
            $table->integer('created');
        });

        Schema::create('affiliate_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('affiliate_uid')->nullable();
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->float('total_withdrawal')->default(0);
            $table->float('total_approved')->nullable();
            $table->float('total_balance')->nullable();
        });

        Schema::create('affiliate_withdrawal', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('affiliate_uid')->nullable();
            $table->float('amount')->nullable();
            $table->text('bank')->nullable();
            $table->text('notes')->nullable();
            $table->integer('status')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('ai_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('desc', 500)->nullable();
            $table->string('icon', 150)->nullable();
            $table->string('color', 30)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('ai_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('team_id')->nullable();
            $table->string('name')->nullable();
            $table->string('accounts', 500)->nullable();
            $table->longText('prompts')->nullable();
            $table->integer('time_post')->nullable();
            $table->integer('end_date')->nullable();
            $table->integer('next_try')->nullable();
            $table->text('data')->nullable();
            $table->string('result', 500)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('team_id')->nullable();
            $table->text('prompt')->nullable();
        });

        Schema::create('ai_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('cate_id')->nullable();
            $table->text('content')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('id_secure', 50)
                  ->nullable()
                  ->unique();
            $table->string('provider');       // openai, claude, gemini, deepseek...
            $table->string('model_key');      // gpt-4o, gpt-5, claude-haiku...
            $table->string('name');           // Friendly name
            $table->string('category')->default('text'); 
            $table->string('type')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->string('api_type')->default('chat')
                  ->comment('API endpoint type: chat, responses, audio, image, video, embedding...');
            $table->json('api_params')->nullable()
                  ->comment('Custom API params mapping, e.g., {"max_tokens":"max_output_tokens"}');
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'model_key', 'category']);
        });
        
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('module')->nullable();
            $table->string('social_network')->nullable();
            $table->string('category')->nullable();
            $table->string('reconnect_url', 255)->nullable();
            $table->integer('team_id')->nullable();
            $table->integer('login_type')->nullable();
            $table->integer('can_post')->nullable();
            $table->string('pid')->nullable();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->text('token')->nullable();
            $table->string('avatar', 500)->nullable();
            $table->string('url')->nullable();
            $table->string('tmp')->nullable();
            $table->mediumText('data')->nullable();
            $table->integer('proxy')->nullable();
            $table->integer('run')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('type', 100)->nullable();
            $table->integer('cate_id')->nullable()->default(0);
            $table->string('slug', 500);
            $table->string('title', 500)->nullable();
            $table->text('desc')->nullable();
            $table->longText('content')->nullable();
            $table->string('thumbnail', 500)->nullable();
            $table->text('custom_1')->nullable();
            $table->text('custom_2')->nullable();
            $table->text('custom_3')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('article_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->text('desc')->nullable();
            $table->string('icon', 150)->nullable();
            $table->string('color', 30)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('article_map_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('article_id')->nullable();
            $table->integer('tag_id')->nullable();
        });

        Schema::create('article_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('slug', 100)->nullable();
            $table->string('desc', 500)->nullable();
            $table->string('icon', 150)->nullable();
            $table->string('color', 30)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key', 191)->primary();
            $table->text('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key', 191)->primary();
            $table->string('owner', 191);
            $table->integer('expiration');
        });

        Schema::create('captions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('team_id')->nullable();
            $table->integer('type')->nullable();
            $table->string('name', 255)->nullable();
            $table->text('content')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('code', 32)->nullable();
            $table->integer('type')->default(1);
            $table->float('discount')->nullable();
            $table->integer('start_date')->nullable();
            $table->integer('end_date')->nullable();
            $table->text('plans')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->nullable();
            $table->integer('status')->default(1);
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('credit_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('team_id')->nullable();
            $table->string('feature', 50)->nullable();
            $table->string('model', 100)->nullable();
            $table->integer('date')->nullable();
            $table->integer('credits_used')->default(0);
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
            $table->unique(['team_id', 'feature', 'model', 'date'], 'team_feature_model_date_unique');
        });

        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('id_secure')->nullable();
            $table->integer('is_folder')->default(0);
            $table->integer('pid')->default(0);
            $table->integer('team_id')->nullable();
            $table->mediumText('name')->nullable();
            $table->mediumText('file')->nullable();
            $table->mediumText('type')->nullable();
            $table->mediumText('extension')->nullable();
            $table->text('detect')->nullable();
            $table->float('size')->nullable();
            $table->integer('is_image')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->mediumText('note')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('team_id')->nullable();
            $table->string('name')->nullable();
            $table->string('color', 32)->nullable();
            $table->longText('accounts')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue', 191);
            $table->longText('payload');
            $table->tinyInteger('attempts')->unsigned()->default(0);
            $table->integer('reserved_at')->unsigned()->nullable();
            $table->integer('available_at')->unsigned()->nullable();
            $table->integer('created_at')->unsigned();
            $table->index('queue');
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_secure', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('code', 10)->nullable();
            $table->string('icon', 32)->nullable();
            $table->string('dir', 3)->nullable();
            $table->integer('is_default')->nullable();
            $table->integer('auto_translate')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('language_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 5);
            $table->text('name');
            $table->text('value')->nullable();
            $table->integer('custom')->default(0);
        });

        Schema::create('module_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('module', 191);
            $table->boolean('enabled')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique('module');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->enum('source', ['auto', 'manual']);
            $table->unsignedBigInteger('mid')->nullable();
            $table->string('type', 50)->default('news');
            $table->text('message')->nullable();
            $table->string('url', 255)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index('mid');
            $table->index('user_id');
        });
        
        Schema::create('notification_manual', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('message');
            $table->string('url', 255)->nullable();
            $table->string('type', 50)->default('news');
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->longText('value')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token', 255);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('payment_getways', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_sercure', 32)->nullable();
            $table->string('name', 250)->nullable();
            $table->string('desc', 500)->nullable();
            $table->string('module', 250)->nullable();
            $table->integer('status')->nullable();
        });

        Schema::create('payment_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('uid')->nullable();
            $table->integer('plan_id')->nullable();
            $table->string('from', 32)->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('currency', 10)->nullable();
            $table->integer('by')->nullable();
            $table->float('amount')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('payment_manual', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('uid')->nullable();
            $table->integer('plan_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->text('payment_info')->nullable();
            $table->float('amount')->nullable();
            $table->string('currency', 10)->nullable();
            $table->text('notes')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('payment_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('uid')->nullable();
            $table->integer('plan_id')->nullable();
            $table->integer('type')->nullable();
            $table->string('service', 200)->nullable();
            $table->string('source', 50)->nullable();
            $table->text('subscription_id')->nullable();
            $table->text('customer_id')->nullable();
            $table->float('amount')->nullable();
            $table->string('currency', 20)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('name', 255)->nullable();
            $table->text('desc')->nullable();
            $table->integer('type')->nullable();
            $table->float('price')->nullable();
            $table->integer('trial_day')->nullable();
            $table->integer('free_plan')->nullable();
            $table->integer('featured')->nullable();
            $table->integer('position')->nullable();
            $table->mediumText('permissions')->nullable();
            $table->mediumText('data')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('team_id')->nullable();
            $table->integer('campaign')->nullable();
            $table->string('labels', 500)->nullable();
            $table->integer('account_id')->nullable();
            $table->string('social_network', 100)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('module', 100)->nullable();
            $table->string('function', 50)->nullable();
            $table->integer('api_type')->nullable();
            $table->string('type', 20)->nullable();
            $table->string('method', 15)->default('basic');
            $table->integer('query_id')->nullable();
            $table->longText('data')->nullable();
            $table->integer('time_post')->nullable();
            $table->integer('delay')->nullable();
            $table->integer('repost_frequency')->nullable();
            $table->integer('repost_until')->nullable();
            $table->longText('result')->nullable();
            $table->string('tmp', 500)->nullable();
            $table->text('custom_data_1')->nullable();
            $table->text('custom_data_2')->nullable();
            $table->text('custom_data_3')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('post_campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('team_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->text('desc')->nullable();
            $table->string('color', 32)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('post_labels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('team_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->text('desc')->nullable();
            $table->string('color', 32)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('post_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->bigInteger('post_id')->nullable();
            $table->bigInteger('team_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->string('social_network', 50)->nullable();
            $table->bigInteger('campaign')->nullable();
            $table->string('method', 15)->nullable();
            $table->integer('query_id')->nullable();
            $table->json('labels')->nullable();
            $table->string('category', 50)->nullable();
            $table->string('module', 128)->nullable();
            $table->string('type', 32)->nullable();
            $table->string('post_social_id', 250)->nullable();
            $table->text('message')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->integer('created')->nullable();
        });

        Schema::create('proxies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('team_id')->default(0);
            $table->integer('is_system')->nullable();
            $table->string('description', 255)->nullable();
            $table->string('proxy', 255)->nullable();
            $table->string('location', 100)->nullable();
            $table->float('limit')->nullable();
            $table->integer('is_free')->nullable();
            $table->integer('active')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('rss_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('team_id')->nullable();
            $table->text('accounts')->nullable();
            $table->string('url', 500);
            $table->string('title', 255)->nullable();
            $table->text('desc')->nullable();
            $table->integer('start_date')->nullable();
            $table->integer('end_date')->nullable();
            $table->integer('time_post')->nullable();
            $table->integer('next_try')->nullable();
            $table->longText('data')->nullable();
            $table->string('stats', 255)->nullable();
            $table->tinyInteger('status')->nullable();
            $table->integer('created')->nullable();
            $table->integer('changed')->nullable();
        });

        Schema::create('rss_schedules_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('schedule_id');
            $table->bigInteger('account_id')->nullable();
            $table->string('post_link', 1000);
            $table->string('post_title', 500)->nullable();
            $table->integer('published_at')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 191)->primary();
            $table->bigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity');
        });

        Schema::create('support_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('name', 255)->nullable();
            $table->text('desc')->nullable();
            $table->string('icon', 150)->nullable();
            $table->string('color', 32)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('support_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('ticket_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->longText('comment')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('support_labels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('desc', 500)->nullable();
            $table->string('icon', 150)->nullable();
            $table->string('color', 30)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('support_map_labels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ticket_id')->nullable();
            $table->integer('label_id')->nullable();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('user_read')->nullable();
            $table->integer('admin_read')->nullable();
            $table->integer('cate_id')->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('team_id')->nullable();
            $table->integer('open_by')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('pin')->nullable();
            $table->string('title', 250)->nullable();
            $table->longText('content')->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('support_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('name', 150)->nullable();
            $table->string('icon', 150)->nullable();
            $table->string('color', 150)->nullable();
            $table->integer('status')->nullable();
            $table->integer('changed')->nullable();
            $table->integer('created')->nullable();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->string('name', 50)->nullable();
            $table->integer('owner')->nullable();
            $table->longText('permissions')->nullable();
            $table->longText('data')->nullable();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_secure', 32)->nullable();
            $table->integer('uid')->nullable();
            $table->integer('team_id')->nullable();
            $table->longText('permissions')->nullable();
            $table->string('invite_token', 50)->nullable();
            $table->string('pending', 255)->nullable();
            $table->integer('status')->nullable();
        });

        DB::table('plans')->insert([
            'id' => 1,
            'id_secure' => substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10),
            'name' => 'Started',
            'desc' => 'Perfect for getting started easily',
            'type' => 1,
            'price' => 99,
            'trial_day' => 7,
            'free_plan' => 1,
            'featured' => 0,
            'position' => 1,
            'permissions' => '[{"key":"credits","label":"Credits","value":"100000"},{"key":"ai_word_credits","label":"Ai Word Credits","value":"1000"},{"key":"ai_media_credits","label":"Ai Media Credits","value":null},{"key":"ai_character_included","label":"Ai Character Included","value":null},{"key":"ai_minutes_included","label":"Ai Minutes Included","value":null},{"key":"appchannels","label":"Channels","value":"1"},{"key":"max_channels","label":"Max channels","value":"-1"},{"key":"channel_calculate_by","label":"Channel Calculate By","value":"1"},{"key":"appchannels.appchannelfacebookpages","label":"Facebook pages","value":"1"},{"key":"appchannels.appchannelfacebookprofiles","label":"Facebook profiles","value":"1"},{"key":"appchannels.appchannelinstagramprofiles","label":"Instagram profiles","value":"1"},{"key":"appchannels.appchannelinstagramunofficial","label":"Instagram Unofficial","value":"1"},{"key":"appchannels.appchannellinkedinpages","label":"Linkedin pages","value":"1"},{"key":"appchannels.appchannellinkedinprofiles","label":"Linkedin profiles","value":"1"},{"key":"appchannels.appchanneltiktokprofiles","label":"Tiktok profiles","value":"1"},{"key":"appchannels.appchannelxprofiles","label":"X profiles","value":"1"},{"key":"appchannels.appchannelxunofficial","label":"X profiles (Unofficial)","value":"1"},{"key":"apppublishing","label":"Publishing","value":"1"},{"key":"apppublishing.max_post","label":"Maximum Posts per Month","value":"100"},{"key":"apppublishingcampaigns","label":"Campaign Publishing","value":"1"},{"key":"apppublishinglabels","label":"Label Publishing","value":"1"},{"key":"apppublishing.appchannelfacebookpages","label":"Facebook pages","value":"1"},{"key":"apppublishing.appchannelfacebookprofiles","label":"Facebook profiles","value":"1"},{"key":"apppublishing.appchannelinstagramprofiles","label":"Instagram profiles","value":"1"},{"key":"apppublishing.appchannelinstagramunofficial","label":"Instagram Unofficial","value":"1"},{"key":"apppublishing.appchannellinkedinpages","label":"Linkedin pages","value":"1"},{"key":"apppublishing.appchannellinkedinprofiles","label":"Linkedin profiles","value":"1"},{"key":"apppublishing.appchanneltiktokprofiles","label":"Tiktok profiles","value":"1"},{"key":"apppublishing.appchannelxprofiles","label":"X profiles","value":"1"},{"key":"apppublishing.appchannelxunofficial","label":"X profiles (Unofficial)","value":"1"},{"key":"appfiles","label":"Files","value":"1"},{"key":"appfiles.google_drive","label":"Google Drive","value":"1"},{"key":"appfiles.dropbox","label":"Dropbox","value":"1"},{"key":"appfiles.onedrive","label":"OneDrive","value":"1"},{"key":"appfiles.image_editor","label":"Max. file size (MB)","value":"1"},{"key":"appfiles.max_storage","label":"Appfiles.max Storage","value":"1000"},{"key":"appfiles.max_size","label":"Appfiles.max Size","value":"100"},{"key":"appaicontents","label":"AI Contents","value":"1"},{"key":"appaipublishing","label":"AI Publishing","value":"1"},{"key":"appbulkpost","label":"Bulk Posts","value":"1"},{"key":"appcaptions","label":"Captions","value":"1"},{"key":"appgroups","label":"Groups","value":"1"},{"key":"appmediasearch","label":"Search Media Online","value":"1"},{"key":"appproxies","label":"Proxies","value":"1"},{"key":"apprssschedules","label":"RSS Schedules","value":"1"},{"key":"appsupport","label":"Support","value":"1"},{"key":"appteams","label":"Teams","value":"1"},{"key":"appurlshorteners","label":"URL Shorteners","value":"1"},{"key":"appwatermark","label":"Watermark","value":"1"}]',
            'data' => null,
            'status' => 1,
            'changed' => time(),
            'created' => time(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('support_types');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('support_map_labels');
        Schema::dropIfExists('support_labels');
        Schema::dropIfExists('support_comments');
        Schema::dropIfExists('support_categories');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('rss_schedules_history');
        Schema::dropIfExists('rss_schedules');
        Schema::dropIfExists('proxies');
        Schema::dropIfExists('post_stats');
        Schema::dropIfExists('post_labels');
        Schema::dropIfExists('post_campaigns');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('payment_subscriptions');
        Schema::dropIfExists('payment_manual');
        Schema::dropIfExists('payment_history');
        Schema::dropIfExists('payment_getways');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('options');
        Schema::dropIfExists('notification_manual');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('module_statuses');
        Schema::dropIfExists('migrations');
        Schema::dropIfExists('language_items');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('files');
        Schema::dropIfExists('credit_usages');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('captions');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('article_tags');
        Schema::dropIfExists('article_map_tags');
        Schema::dropIfExists('article_categories');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('ai_templates');
        Schema::dropIfExists('ai_prompts');
        Schema::dropIfExists('ai_posts');
        Schema::dropIfExists('ai_categories');
        Schema::dropIfExists('affiliate_withdrawal');
        Schema::dropIfExists('affiliate_info');
        Schema::dropIfExists('affiliate');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('users');
    }
};
