<?php

use App\Models\WordCloud;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWordCloudsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('word_clouds', function (Blueprint $table) {
            $table->id();
            $table->string('eventName'); //Мероприятие
            $table->string('question'); // Вопрос
            $table->string('backgroundImage')->default(''); //Фоновая картинка
            $table->enum('wordColor', WordCloud::getColorsKeys())->default('pastel1'); // Политра цветов
            $table->string('exceptionWords')->nullable(); //Слова исключения
            $table->string('pageTitle')->default('Облако слов'); // Title страницы
            $table->string('logo')->nullable(); //Лого для формы
            $table->unsignedBigInteger('createUser_id')->nullable(); //Создатель облака
            $table->boolean('isUnique')->default(false); //Проверка на уникальность
            $table->text('additionalCss')->nullable(); // Дополнительный CSS
            $table->integer('fontSizeSmall')->default(30); // Минимальный размер текста
            $table->integer('fontSizeLarge')->default(120); // Максимальный размер текста
            $table->integer('angle')->default(0); // Угол поворота текста
            $table->boolean('showCounts')->default(false); // Выводить кол-во
            $table->integer('countSize')->default(20); // Размер кол-ва
            $table->foreign('createUser_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('word_clouds');
    }
}
