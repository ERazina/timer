<?php
/* @var $this \ProfileController */
/* @var $user \User */
/* @var $registrationModel \application\lib\rw\forms\RegistrationBonusCard */
/* @var $form \CActiveForm */
 
?>
 

<?
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'RegistrationBonusCard-form',
    'enableAjaxValidation' => false,
    'htmlOptions' => array(
        'class' => 'bcard__form',
    ),
));
?>

<div class="plain-error error-summary-normalize">
    <?= $form->errorSummary($registrationModel) ?>
</div>

<?php foreach (['last_name', 'first_name', 'middle_name'] as $field): ?>
    <div class="field">
        <label for="<?php echo $field; ?>">
            <?php echo $registrationModel->getAttributeLabel($field) ?>
        </label>
        <?=
        $form->textField( $registrationModel, $field, [
            'class'         => 'text focusable',
            'name'          => 'RegistrationBonusCard[' . $field . ']',
            'data-field'    => $field,
            'required'      => true
        ])
        ?>
    </div>
<?php endforeach; ?>

<?php foreach (['phone' => $user->phone, 'email' => $user->outer_email] as $field => $value): ?>
    <?php //TODO Класс neadCheck - означает что данные данные проверяем через веритификацию по коду ?>
    <div id='<?= $field ?>Form' class="field neadCheck<?php if ($field === 'phone') echo " phone"; ?>">
        <label for="<?php echo $field; ?>">
            <?php echo $registrationModel->getAttributeLabel($field) ?>
        </label>
        <?php
        echo $form->textField(  $registrationModel, $field, [
            'class' => 'text focusable check-value',
            'name' => 'RegistrationBonusCard[' . $field . ']',
            'data-current' => $value,
            'data-field' => $field,
            'placeholder' => $field === 'phone' ? '+7 (___) ___-____' : null
        ]);

        $confirmField = 'confirm' . ucfirst($field);
        echo $form->hiddenField($registrationModel, $confirmField, [
            'name' => 'RegistrationBonusCard[' . $confirmField . ']',
            'class' => 'confirm-hidden',
        ]);
        ?>

        <?php if (!$registrationModel->$confirmField): ?>
        <button id="confirmButton" type="button" class="validate-field <?= $field ?>">Подтвердить</button>
        <span class="confirm-span">Получить повторно через <span id="seconds"></span> сек.</span>
        <?php endif; ?>

        <div class="field hidden">
            <label for="<?php echo "confirm-".$field; ?>">
                <?php if ($field === 'phone'): ?>
                    Введите код из смс
                <?php else: ?>
                    Введите код из почты
                <?php endif; ?>
                    <span class="helper">?
                        <span class="helper-content">
                            Если вы не получили код подтверждения на телефон или
                            электронную почту, то сообщите о проблеме по телефону
                            8 800 775 21 00, и мы обязательно её решим <br><br>
                            Если вам не удалось привязать карту в личном кабинете,
                            то вы можете оформить заказ и вернуться к данному вопросу
                            в любое удобное время.
                        </span>
                    </span>
            </label>
            <?php
            echo \CHtml::textField('code', '', [
                'class' => 'text focusable confirm-code',
                'id' => 'confirm-'.$field
            ]);
            ?>
            <button type="button" class="btn-confirm-code">OK</button>
        </div>
        <div class="confirmed">OK</div>
    </div>

<?php endforeach; ?>

<div class="field">
    <label for="RegistrationBonusCard_birthday">
        Дата рождения
    </label> 
    <?=
    $form->textField(
        $registrationModel, 'birthday', array(
            'id' => 'datepicker',
            'class' => 'text focusable',
            'name' => 'RegistrationBonusCard[birthday]',
            'placeholder' => '__/__/____'
        )
    )
    ?>
</div>

<div class="field">
    <label>
        Пол
    </label>
    <?=
    $form->radioButtonList(
        $registrationModel, 'gender', [
        'male' => 'Мужской', 'female' => 'Женский',
    ], [
            'class' => 'text focusable',
            'name' => 'RegistrationBonusCard[gender]',
        ]
    )
    ?>
</div>

<div class="field">
    <?=
    $form->checkbox(
        $registrationModel, 'consentParticipate',
        array('id' => 'consentParticipate',
            'name' => 'RegistrationBonusCard[consentParticipate]'
        )
    )
    ?>
    <label for="consentParticipate">
        Подтверждаю согласие на <a href="http://club.tehnosila.ru/about/rules" target="_blank">условия программы лояльности</a>
    </label>
</div>

<div class="field">
    <button id="test-old-card" class="submit button yellow pressable<?php if( !$registrationModel->consentParticipate ) echo " disabled" ?>"<?php if( !$registrationModel->consentParticipate ) echo " disabled" ?>>Зарегистрировать карту</button>
</div>

<? $this->endWidget() ?>

<script type="text/javascript">
    $(function () {

        $('#datepicker').mask('99/99/9999');
        $('#RegistrationBonusCard_phone').mask('+7 (999) 999-99-99');

        $('#RegistrationBonusCard-form').on('keyup', function (e) {
            e.stopPropagation();

            if (e.keyCode == 13) e.preventDefault();

        }).on('keyup change', '.check-value', function (e) {
            e.stopPropagation();

            $(this).siblings('.validate-field').fadeIn();
            $(this).siblings('.confirmed').fadeOut();

        }).on('click', '.validate-field', function (e) {
            e.stopPropagation();

            var _self = $(this),
                _input = $(this).siblings('.check-value'),
                _hidden = $(this).next(),
                _val = $(this).hasClass('phone') ? _input.val().replace(/[\s-()]/g, '') : _input.val();

            $.get('<?php echo $this->createUrl('/profile/bonuscardValidationCode') ?>',
                {value: _val, field: _input.attr('data-field')},
                function (data) {

                    _self.siblings('label').find('div.error').remove();

                    if (data.result === 'success') {

                        _hidden.fadeIn();
                    } else {

                        _self.siblings('label').append('<div class="error" style="padding-top:0;font-size:11px;">'+ data.errors.all[0].title +'</div>');
                    }
                }
            )

        }).on('click', '.btn-confirm-code', function (e) {
            e.stopPropagation();

            var _self = $(this),
                _input = $(this).parent().siblings('.check-value'),
                _code = $(this).prev('.confirm-code').val(),
                _val = _input.attr('data-field') === 'phone' ? _input.val().replace(/[\s-()]/g, '') : _input.val();

            $.get('<?php echo $this->createUrl('/profile/BonuscardCheckValidationCode') ?>',
                {value: _val, code: _code, field: _input.attr('data-field')},
                function (data) {

                    _self.siblings('label').find('div.error').remove();

                    if (data.result === 'success' && data.isValid) {

                        if (_input.hasClass('error')) _input.removeClass('error');

                        _self.parent().hide();
                        _self.parent().prev().hide();
                        _self.parent().next().fadeIn();
                        _self.parent().siblings('.confirm-hidden').val(1);

                    } else {

                        _self.siblings('label').append('<div class="error" style="padding-top:0;font-size:11px;">'+ data.errors.title +'</div>');
                    }
                }
            )
        }).on('change', '#consentParticipate', function (e) {

            var _btn = $('#test-old-card');

            _btn.toggleClass('disabled');
            if (_btn.attr('disabled')) {

                _btn.attr('disabled', false)
            } else {

                _btn.attr('disabled', true)
            }
        });
        
        /**
         * 
         * @returns {function}
         */
        function getRegisterNewCardAction() {
            if(!window['registerNewCardAction']) {
                window['registerNewCardAction'] = function (){ return this.submit(); }
            }   
            return  window['registerNewCardAction'];
        }

        $('#test-old-card').click(function () {
            var form = $(this.form);

            $(this).attr('disabled', true);

            $.post('<?php echo $this->createUrl('/profile/bonuscardsearch')?>', form.serialize(), function (data) {

                if (data.isset) {

                    $('#popup-check-card .popup-title p').text('№ '+ data.card_number.replace(/(\d\d\d\d)(\d\d\d\d)(\d+)/g, '$1-$2-$3') );
                    showPopup('check-card');
                    $('#popup-check-card').on('click', '.yellow-flat', function (e) {
                        e.stopPropagation();

                        sessionStorage.setItem('bcard-num', data.card_number);
                        location.href = '/profile/bonuscardbind';
                    }).on('click', '.white-flat', function (e) {
                        e.stopPropagation();

                        getRegisterNewCardAction().apply(form, []);
                    });

                } else {
                    getRegisterNewCardAction().apply(form, []);
                }
            });
            return false;
        });

//таймер на phoneForm на 300 секунд
        $('#phoneForm').ready(function(){
            $(this).find('#confirmButton').click(function(){
                function myClock(i) {
                    if(i>0){
                        i = i - 1;
                        $('#phoneForm').find('#seconds').html(i);

                    }
                    return i;
                    

                };

                $(this).hide();
                $('#phoneForm').find('.confirm-span').show();
                $('#phoneForm').find('.seconds').show();
                i = 301;
                setTimeout(function run() {
                    i = myClock(i);
                    if(i>0){
                        setTimeout(run, 1000);
                    }
                    else{

                        $('#phoneForm').find('#confirmButton').show();
                        $('#phoneForm').find('.confirm-span').hide();
                        $('#phoneForm').find('#seconds').html('300');
                        
                    }
                });

            myClock();

            });
        });

//таймер на emailForm на 60 секунд
        $('#emailForm').ready(function(){
            $(this).find('.email').click(function(){
                function myClock1(x) {
                    if(x>0){
                        x = x - 1;
                        $('#emailForm').find('#seconds').html(x);

                    }
                    return x;
                    
                };

                $(this).hide();
                $('#emailForm').find('.confirm-span').show();
                $('#emailForm').find('#seconds').show('60');
                a = 61;
                setTimeout(function run() {
                    a = myClock1(a);
                    if(a>0){
                        setTimeout(run, 1000);
                    }
                    else{

                        $('#emailForm').find('#confirmButton').show();
                        $('#emailForm').find('.confirm-span').hide();
                        $('#emailForm').find('#seconds').html('60');
                        
                    }
                });

            myClock1();

            });
        });
    });

</script>
 

