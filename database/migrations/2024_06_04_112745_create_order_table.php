<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
                $table->id('id');
                $table->unsignedBigInteger('user_id')->nullable(); // Adding user_id column
                $table->dateTime('order_date');
                $table->enum('status', ['1', '2', '3', '4', '5'])->default('1')->comment('1->Pending, 2->Process, 3->Shipped, 4->Completed, 5->Cancelled');
                $table->string('order_number')->unique();
                $table->float('sub_total');
                $table->unsignedBigInteger('shipping_id')->nullable();
                $table->float('coupon')->nullable();
                $table->float('total_amount');
                $table->integer('quantity');
                $table->enum('payment_method', ['cod', 'paypal'])->default('cod');
                $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');
               
                // Adding foreign key constraints
                // $table->foreign('user_id')->nullable();
                // $table->foreign('shipping_id')->nullable();
                
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email');
                $table->string('phone');
                $table->string('country');
                $table->string('post_code')->nullable();
                $table->text('address1');
                $table->text('address2')->nullable();
                $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order');
    }
}
