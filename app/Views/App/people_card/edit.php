<?= $this->layout("layouts/app_layout", [
        "title" => $title ?? "Editar Pessoa | " . APP_NAME,
        "active" => "pessoas-cartao",
]) ?>

<?php $linkedIds = $cardUser->getLinkedCardIds(); ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-6">Editar Pessoa no Cartão</h4>

    <?= \App\Core\Message::render() ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= url('/pessoas-cartao/' . $cardUser->getId() . '/editar') ?>" method="post">
                <?= csrf_input() ?>

                <div class="mb-6">
                    <label class="form-label">Cartões <small class="text-body-secondary">(opcional — marque os que essa
                            pessoa usa)</small></label>
                    <?php if (empty($cards)): ?>
                        <p class="text-body-secondary mb-0">
                            Você ainda não tem cartões cadastrados. Pode salvar a pessoa mesmo assim
                            e vincular um cartão depois.
                        </p>
                    <?php else: ?>
                        <?php foreach ($cards as $card): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="credit_card_ids[]"
                                       id="card_<?= $card->getId() ?>" value="<?= $card->getId() ?>"
                                        <?= in_array($card->getId(), $linkedIds, true) ? 'checked' : '' ?>/>
                                <label class="form-check-label" for="card_<?= $card->getId() ?>">
                                    <?= htmlspecialchars($card->getName()) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>


                <div class="mb-6">
                    <label for="name" class="form-label">Nome da Pessoa</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?= old('name', $cardUser->getName()) ?>" required/>
                </div>

                <div class="mb-6">
                    <label for="phone" class="form-label">Telefone <small class="text-body-secondary">(opcional)</small></label>
                    <input type="text" class="form-control phone-mask" id="phone" name="phone"
                           value="<?= old('phone', $cardUser->getPhone()) ?>"/>
                </div>

                <div class="mb-6">
                    <label for="notes" class="form-label">Observações <small
                                class="text-body-secondary">(opcional)</small></label>
                    <textarea class="form-control" id="notes" name="notes"
                              rows="3"><?= old('notes', $cardUser->getNotes()) ?></textarea>
                </div>

                <button class="btn btn-primary" type="submit">Salvar Alterações</button>
                <a href="<?= url('/pessoas-cartao') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>